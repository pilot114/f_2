<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use App\Common\Exception\StaticException;
use App\Common\Service\File\TempFileRegistry;
use App\Domain\Portal\Files\Enum\ImageResizeType;
use App\Domain\Portal\Files\Enum\ImageSize;
use App\Domain\Portal\Security\Entity\SecurityUser;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Клиент для сервиса хранения файлов
 */
class StaticClient
{
    public const MAX_FILE_SIZE = 8 * 1024 * 1024;

    public function __construct(
        public readonly string        $url,
        protected string              $secret,
        protected HttpClientInterface $httpClient,
        protected SecurityUser        $currentUser,
        protected TempFileRegistry    $tmpFileRegistry,
    ) {
    }

    public function uploadFile(
        File $file,
        string $name,
        string $directory = '/default/',
        // пока непонятно, будем ли использовать эти параметры в дальнейшем
        bool $isRewrite = false,
        array $resizeSettings = [],
    ): string {
        $formFields = [
            'directory'  => $directory,
            'public'     => 'true',
            'force'      => $isRewrite ? '1' : '0',
            'resizeData' => $resizeSettings ?: json_encode($resizeSettings),
        ];

        if ($file->getSize() < StaticClient::MAX_FILE_SIZE) {
            return $this->uploadRequest($isRewrite, $name, $formFields, $file);
        }

        // заливаем файл по частям, если он слишком большой
        $chunkSize = StaticClient::MAX_FILE_SIZE - (1024 * 1024);
        $formFields['isFileStream'] = '1';
        $formFields['chunkCount'] = intval(ceil($file->getSize() / $chunkSize));
        $formFields['fileName'] = $file->getBasename();
        $handle = fopen($file->getPathname(), 'rb');
        if (!$handle) {
            throw new RuntimeException("Не удалось открыть файл {$file->getPathname()} для чтения");
        }
        $path = '';
        for ($chunkNumber = 1; $chunkNumber <= $formFields['chunkCount']; $chunkNumber++) {
            $formFields['chunkNumber'] = $chunkNumber;

            if ($chunkData = fread($handle, $chunkSize)) {
                $chunkFile = $this->tmpFileRegistry->createFile($chunkData);
                $path = $this->uploadRequest($isRewrite, $name, $formFields, $chunkFile);
            }
        }
        fclose($handle);
        return $path;
    }

    public function removeFile(string $removeFilePath): bool
    {
        $parts = explode('/', $removeFilePath);
        $name = array_pop($parts);

        // костыль изза автоподставления '/public' на стороне статика
        if (isset($parts[0]) && $parts[0] === 'public') {
            unset($parts[0]);
        }
        if (isset($parts[1]) && $parts[1] === 'public') {
            unset($parts[1]);
        }

        $url = sprintf('%s/v1/file/delete?login=%s&type=emp&public=true', $this->url, $this->currentUser->id);
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'secret-key' => $this->secret,
            ],
            'body' => [
                'directory' => implode('/', $parts),
                'name'      => $name,
            ],
        ]);

        $result = $response->toArray(false);

        return $result['status'] ?? false;
    }

    public function convertToSafeName(string $originalName): string
    {
        // обрабатываем случай, если передан путь до файла
        $str = mb_strtolower(basename($originalName));

        // переводим, что возможно, в латиницу
        $mapped = strtr($str, [
            " " => "_",
            "а" => "a",
            "б" => "b",
            "в" => "v",
            "г" => "g",
            "д" => "d",
            "е" => "e",
            "ё" => "e",
            "ж" => "zh",
            "з" => "z",
            "и" => "i",
            "й" => "y",
            "к" => "k",
            "л" => "l",
            "м" => "m",
            "н" => "n",
            "о" => "o",
            "п" => "p",
            "р" => "r",
            "с" => "s",
            "т" => "t",
            "у" => "u",
            "ф" => "f",
            "х" => "h",
            "ц" => "ts",
            "ч" => "ch",
            "ш" => "sh",
            "щ" => "shch",
            "ъ" => "",
            "ы" => "y",
            "ь" => "",
            "э" => "e",
            "ю" => "yu",
            "я" => "ya",
        ]);

        // всё что не латиница, цифра, точка, тире или подчеркивание - вырезаем
        $trimmed = preg_replace('/[^a-zA-Z0-9._-]/', '', $mapped);

        // если от имени ничего не осталось, просто генерим идентификатор
        return $trimmed ?: substr(md5($originalName), 0, 8);
    }

    /**
     * return format:
     * https://static.siberianhealth.com/public/cp_userpic/_resize/6387_fit_80_80.jpg
     */
    public static function getUserpicByUserId(int $empId, ImageSize $size): string
    {
        $defaultUrl = "https://static.siberianhealth.com/public/cp_userpic/$empId.jpg";
        return StaticClient::getResizeUrl($defaultUrl, $size);
    }

    /**
     * https://static.siberianhealth.com/public/cp_userpic/4026.jpg
     * =>
     * https://static.siberianhealth.com/public/cp_userpic/_resize/4026_fit_80_80.jpg
     *
     * @param $url - путь к файлу на static
     */
    public static function getResizeUrl(string $url, ImageSize $size, ImageResizeType $type = ImageResizeType::FIT): string
    {
        $parts = explode('/', $url);
        $exploded = explode('.', array_pop($parts));
        $name = $exploded[0] ?? null;
        $ext = $exploded[1] ?? '.jpg';
        $resizeSuffix = sprintf(
            '/_resize/%s_%s_%s_%s.%s',
            $name, $type->value, $size->value, $size->value, $ext
        );
        return implode('/', $parts) . $resizeSuffix;
    }

    private function uploadRequest(bool $isRewrite, string $name, array $formFields, File $file): string
    {
        // TODO: Выяснить как будем дальше работать. Сейчас если мы перезаписываем файл, то нам нужно использовать его настоящее имя.
        $hiddenName = $isRewrite ? $name : uniqid() . "_$name";

        $formData = $this->getForm($formFields, $file, $hiddenName);

        $url = sprintf('%s/v1/file/create?login=%s&type=emp', $this->url, $this->currentUser->id);
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'secret-key' => $this->secret,
                ...$formData->getPreparedHeaders()->toArray(),
            ],
            'body' => $formData->bodyToString(),
        ]);

        $data = $response->getContent();
        /** @var array $result */
        $result = json_decode($data, true);

        if (isset($result['status'], $result['data']['uri']) && $result['status']) {
            return $result['data']['uri'];
        }
        if (isset($result['status'], $result['data']['chunkNumber'])) {
            return (string) $result['data']['chunkNumber'];
        }

        throw new StaticException($result['data'] ?? [
            'name'    => $name,
            'message' => 'Не удалось загрузить файл',
        ]);
    }

    private function getForm(array $formFields, File $file, string $name): FormDataPart
    {
        $formFields = array_map(static fn ($value): string => (string) $value, $formFields);

        $resource = fopen($file->getPathname(), 'r');
        if ($resource === false) {
            throw new RuntimeException("Не удалось открыть файл {$file->getPathname()} для чтения");
        }

        $formFields['data'] = new DataPart(
            $resource,
            $this->convertToSafeName($name),
            $file->getMimeType() ?: 'application/octet-stream'
        );
        return new FormDataPart($formFields);
    }
}
