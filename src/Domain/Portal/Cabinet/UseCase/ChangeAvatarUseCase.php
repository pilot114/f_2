<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Common\Service\File\AvatarService;
use App\Common\Service\File\ImageBase64;
use App\Domain\Portal\Files\Entity\File;

class ChangeAvatarUseCase
{
    public function __construct(
        private AvatarService $fileService,
        private ImageBase64 $imageBase64,
    ) {
    }

    public function changeAvatar(string $imageBase64, int $userId): File
    {
        $tempFile = $this->imageBase64->baseToFile($imageBase64);
        $existedFile = $this->fileService->getAvatar($userId);
        return $this->fileService->commonUpload($tempFile, File::USERPIC_COLLECTION, $existedFile, $userId, true);
    }
}
