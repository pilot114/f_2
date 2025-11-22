<?php

declare(strict_types=1);

namespace App\Common\Service\File;

use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Files\Enum\ImageResizeType;
use App\Domain\Portal\Files\Enum\ImageSize;
use App\Domain\Portal\Files\Repository\FileCommandRepository;
use App\Domain\Portal\Files\Repository\FileQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Связывает статик и test.cp_files, обеспечивая консистентность хранения файлов
 */
class FileService
{
    public function __construct(
        protected StaticClient        $client,
        public SecurityUser           $currentUser,
        protected FileQueryRepository $readRepo,
        private FileCommandRepository $writeRepo,
    ) {
    }

    public static function getCpFileUrlDownload(int $fileId): string
    {
        return "/api/v2/file/$fileId/download";
    }

    public static function getCpFileUrlView(int $fileId, ?DateTimeImmutable $expireDate = null, ?ImageSize $size = null): string
    {
        $fitPart = match ($size) {
            ImageSize::MINI   => '/fit_60',
            ImageSize::SMALL  => '/fit_80',
            ImageSize::MEDIUM => '/fit_400',
            ImageSize::BIG    => '/fit_800',
            default           => null,
        };

        $url = "/api/v2/file/$fileId/view" . $fitPart;
        if ($expireDate instanceof DateTimeImmutable) {
            $url .= '?t=' . $expireDate->getTimestamp();
        }
        return $url;
    }

    public function getStaticUrl(File $file, ?string $resizeString = null): string
    {
        $url = $this->client->url . $file->getFilePath();
        if ($resizeString !== null && $file->isImage() && $file->isUserpic()) {
            [$type, $size] = explode('_', $resizeString);
            $url = StaticClient::getResizeUrl(
                $url,
                ImageSize::from((int) $size),
                ImageResizeType::from($type)
            );
        }

        return $url;
    }

    public function getResizedImage(File $file, string $resizeString): Image
    {
        $url = $this->client->url . $file->getFilePath();
        [$type, $size] = explode('_', $resizeString);
        $size = ImageSize::from((int) $size);
        $type = ImageResizeType::from($type);
        $manager = new ImageManager([
            'driver' => 'gd',
        ]);

        if ($type === ImageResizeType::CROP) {
            return $manager->make($url)->crop($size->value, $size->value);
        }

        return $manager->make($url)->fit($size->value, $size->value);
    }

    public function getCacheHeaders(File $file, ?string $resizeString = null): array
    {
        $url = $this->getStaticUrl($file, $resizeString);

        $headers = get_headers($url, true);
        $lastModified = $headers['Last-Modified'] ?? null;
        $cacheControl = $headers['Cache-Control'] ?? null;

        $maxAge = null;
        if ($cacheControl && preg_match('/max-age=(\d+)/', $cacheControl, $matches)) {
            $maxAge = (int) $matches[1];
        }

        return [
            'last_modified' => $lastModified ? new DateTimeImmutable($lastModified) : null,
            'max_age'       => $maxAge ?: 0,
        ];
    }

    public function getById(int $fileId, ?int $userId = null): ?File
    {
        if ($userId === null) {
            return $this->readRepo->findOneBy([
                'id'           => $fileId,
                'is_on_static' => 1,
            ]);
        }
        return $this->readRepo->findOneBy([
            'id'           => $fileId,
            'idemp'        => $userId,
            'is_on_static' => 1,
        ]);
    }

    /**
     * @return Enumerable<int, File>
     */
    public function getFileListByUserId(int $userId, ?string $collectionName = null): Enumerable
    {
        if ($collectionName === null) {
            return $this->readRepo->findBy([
                'idemp'        => $userId,
                'is_on_static' => 1,
            ]);
        }
        return $this->readRepo->findBy([
            'idemp'        => $userId,
            'parent_tbl'   => $collectionName,
            'is_on_static' => 1,
        ]);
    }

    public function commonDelete(int $fileId, int $userId): bool
    {
        $file = $this->readRepo->findOneBy([
            'id'           => $fileId,
            'idemp'        => $userId,
            'is_on_static' => 1,
        ]);
        if ($file === null) {
            return false;
        }
        $result = $this->client->removeFile($file->getFilePath());
        if ($result === false) {
            return false;
        }
        return $this->writeRepo->delete($fileId);
    }

    public function commonUpload(
        SymfonyFile $file,
        string $collectionName,
        ?File $existedFile = null,
        ?int $idInCollection = null,
        bool $isRewrite = false
    ): File {
        FixedIdCollections::check($collectionName, $idInCollection);
        $directory = $this->collectionToDirectory($collectionName);

        if ($file instanceof UploadedFile) {
            $originalName = $file->getClientOriginalName();
        } else {
            $originalName = $file->getBasename();
        }

        $baseOriginalName = pathinfo($originalName, PATHINFO_FILENAME);

        $mimeType = explode('/', $file->getMimeType() ?: 'application/octet-stream')[1];

        $extension = mb_strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extension = substr($extension ?: $mimeType, 0, 10);

        // TODO: Названия аватарок хранятся в определённом формате - $userId . '.jpg'. На этот пусть рассчитывают старые части нашей системы
        // для начала нужно подготовить систему к новому формату, пока будет сохранять аватарки по старому.
        $freePath = $collectionName === File::USERPIC_COLLECTION
            ? $this->currentUser->id . '.jpg'
            : $this->getFreePath($originalName, $directory);

        $staticUrl = $this->client->uploadFile($file, $freePath, $directory, $isRewrite);

        if ($existedFile instanceof File) {
            $existedFile->updateFileData($staticUrl, $baseOriginalName, $extension);
            return $this->writeRepo->update($existedFile);
        }

        return $this->writeRepo->create(new File(
            id: Loader::ID_FOR_INSERT,
            name: $baseOriginalName,
            filePath: $staticUrl,
            userId: $this->currentUser->id,
            idInCollection: $idInCollection ?: $this->readRepo->getNextIdByCollectionName($collectionName),
            collectionName: $collectionName,
            extension: $extension,
        ));
    }

    /**
     * Возвращает не занятое имя файла в указанной директории на статике.
     * Если такой файл ещё не создан, возвращает имя без изменений.
     * Если создан - добавляет номер. Пример: a.txt => a_1.txt, a_2.txt и т.д.
     *
     * @param string $fileName - имя файла с расширением, например cat.jpg
     * @param string $directory - директория на сервере, куда вы хотите сохранить файл.
     * Обязательно указывать начальный и завершающий слэши, такой формат оставлен для удобства работы
     * так как совпадает с форматом клиента. Пример: "/cp_userpic/".
     */
    private function getFreePath(string $fileName, string $directory): string
    {
        $realName = $this->client->convertToSafeName($fileName);
        $filePath = "/public$directory$realName";

        if (!$this->readRepo->fileExists($filePath)) {
            return $fileName;
        }

        $parts = explode('.', $fileName);
        $extensionString = str_contains($fileName, '.') ? array_pop($parts) : '';
        $name = implode('.', $parts);
        $prefixNumber = 1;
        while ($this->readRepo->fileExists($filePath)) {
            $fileName = sprintf("%s_%s.%s", $name, $prefixNumber, $extensionString);
            $realName = $this->client->convertToSafeName($fileName);
            $filePath = "/public$directory$realName";
            $prefixNumber++;
        }

        return $fileName;
    }

    /**
     * Связь между test.cp_files.parent_tbl и директориями на статике
     */
    private function collectionToDirectory(string $collection): string
    {
        return match ($collection) {
            'userpic' => '/cp_userpic/',
            default   => "/$collection/",
        };
    }
}
