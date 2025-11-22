<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Controller;

use App\Common\Service\File\FileService;
use App\Domain\Portal\Files\Dto\ChunkFileRequest;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FileWriteController extends AbstractController
{
    public function __construct(
        private FileService $fileService,
        private SecurityUser $currentUser,
        private Filesystem $filesystem,
        private Finder $finder,
        private string $tmpDir,
    ) {
    }

    #[Route('/api/v2/file/upload', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        // загрузка чанками. Собираем файл из частей
        if ($request->files->has('chunk')) {
            /**
             * @var UploadedFile $chunk
             */
            $chunk = $request->files->get('chunk');
            if (!$chunk->isValid()) {
                throw new BadRequestHttpException('Ошибка при загрузке части файла');
            }
            /** @var string $info */
            $info = $request->request->get('info') ?? '';
            /** @var array $data */
            $data = json_decode($info, true) ?? [];
            $info = new ChunkFileRequest(...$data);

            $tempUploadDir = $this->tmpDir . '/' . $info->uploadId;
            $this->filesystem->mkdir($tempUploadDir);
            $chunk->move($tempUploadDir, (string) $info->chunkIndex);

            // промежуточный чанк
            $uploadedCount = $this->finder->files()->in($tempUploadDir)->count();
            if ($uploadedCount !== $info->chunkCount) {
                return $this->buildJsonResponse(true);
            }

            // последний чанк. Подготавливаем стандартный request
            $uploadedFile = $this->assembleFile($info->fileName, $info->chunkCount, $tempUploadDir);

            $request->files->set('file', $uploadedFile);
            $request->request->set('collection', $info->collection);
            $request->request->set('idInCollection', $info->idInCollection);
        }

        /**
         * @var ?UploadedFile $file
         */
        $file = $request->files->get('file');
        if (!$file) {
            throw new BadRequestHttpException('Не указан параметр file');
        }
        if (!$file->isValid()) {
            throw new BadRequestHttpException('Ошибка при загрузке файла');
        }

        $collection = (string) $request->request->get('collection');
        if ($collection === '') {
            throw new BadRequestHttpException('Не указан параметр collection');
        }

        $idInCollection = (int) $request->request->get('idInCollection');
        $idInCollection = $idInCollection ?: null;

        $file = $this->fileService->commonUpload($file, $collection, idInCollection: $idInCollection);

        return $this->buildJsonResponse($file->toArray());
    }

    private function assembleFile(string $fileName, int $chunkCount, string $tempUploadDir): UploadedFile
    {
        $finalFilePath = $tempUploadDir . '/' . uniqid();
        /** @var resource $finalFile */
        $finalFile = fopen($finalFilePath, 'ab');

        for ($i = 1; $i <= $chunkCount; $i++) {
            $currentChunkPath = $tempUploadDir . '/' . $i;
            /** @var string $chunkContent */
            $chunkContent = file_get_contents($currentChunkPath);
            fwrite($finalFile, $chunkContent);
            unlink($currentChunkPath);
        }
        fclose($finalFile);

        return new UploadedFile($finalFilePath, $fileName, test: true);
    }

    #[Route('/api/v2/file/{fileId}/delete', name: 'file_delete', methods: ['DELETE'])]
    public function delete(int $fileId): JsonResponse
    {
        $result = $this->fileService->commonDelete($fileId ,$this->currentUser->id);
        return $this->buildJsonResponse($result);
    }
}
