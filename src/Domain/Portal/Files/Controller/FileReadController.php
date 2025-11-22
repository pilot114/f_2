<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Controller;

use App\Common\Service\File\FileService;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

// TODO: проверка прав на просмотр
class FileReadController extends AbstractController
{
    public function __construct(
        private FileService $fileService,
        private SecurityUser $currentUser,
    ) {
    }

    #[Route('/api/v2/file/search', name: 'file_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        /**
         * @var ?string $ownerId
         */
        $ownerId = $request->files->get('ownerId');
        /**
         * @var ?string $collectionName
         */
        $collectionName = $request->files->get('collectionName');
        $userId = $ownerId ? (int) $ownerId : $this->currentUser->id;

        $files = $this->fileService->getFileListByUserId($userId, $collectionName);
        $files = $files->map(static fn (File $x): array => $x->toArray());
        return $this->buildJsonResponse([
            'items' => $files,
            'total' => count($files),
        ]);
    }

    /**
     * Вернёт оригинальный файл
     */
    #[Route('/api/v2/file/{fileId}/download', name: 'file_download', methods: ['GET'])]
    public function download(int $fileId): StreamedResponse
    {
        $file = $this->fileService->getById($fileId);
        if (!$file instanceof File) {
            throw new NotFoundHttpException("Файл с id $fileId не найден");
        }
        $staticUrl = $this->fileService->getStaticUrl($file);

        return $this->buildStreamedResponse($staticUrl, $file->getNameForDownload());
    }

    #[Route('/api/v2/file/{fileId}/view/{resizeString?}', name: 'file_view', methods: ['GET'])]
    public function view(int $fileId, Request $request, ?string $resizeString = null): StreamedResponse|Response
    {
        $file = $this->fileService->getById($fileId);
        if (!$file instanceof File) {
            throw new NotFoundHttpException("Файл с id $fileId не найден");
        }

        $headers = $this->fileService->getCacheHeaders($file, $resizeString);
        $response = new Response();
        $cacheControl = [
            'max_age'         => $headers['max_age'],
            'must_revalidate' => true,
            'last_modified'   => $headers['last_modified'],
        ];
        $response->setCache($cacheControl);

        if ($response->isNotModified($request)) {
            return $response->send();
        }

        if ($file->isImage() && $resizeString && !$file->isUserpic()) {
            $image = $this->fileService->getResizedImage($file, $resizeString);
            return $this->buildResizedImageStreamedResponse(
                image: $image,
                name: $file->getNameForDownload(),
                cacheControl: $cacheControl,
                isInline: true
            );
        }

        $staticUrl = $this->fileService->getStaticUrl($file, $resizeString);

        return $this->buildStreamedResponse($staticUrl, $file->getNameForDownload(), $cacheControl, isInline: true);
    }

    #[Route('/api/v2/file/{fileId}/info', name: 'file_info', methods: ['GET'])]
    public function info(int $fileId): JsonResponse
    {
        $file = $this->fileService->getById($fileId);
        if (!$file instanceof File) {
            throw new NotFoundHttpException("Файл с id $fileId не найден");
        }

        return $this->buildJsonResponse($file->toArray());
    }
}
