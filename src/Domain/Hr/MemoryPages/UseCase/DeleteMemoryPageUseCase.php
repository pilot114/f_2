<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use Database\Connection\TransactionInterface;

class DeleteMemoryPageUseCase
{
    public function __construct(
        private MemoryPageQueryRepository $memoryPageQueryRepository,
        private MemoryPageCommandRepository $memoryPageCommandRepository,
        private MemoryPagePhotoService $photoService,
        private MemoryPagePhotoQueryRepository $photoQueryRepository,
        private CommentCommandRepository $commentCommandRepository,
        private WorkPeriodsCommandRepository $workPeriodsCommandRepository,
        private TransactionInterface $transaction
    ) {
    }

    public function deleteMemoryPage(int $memoryPageId): void
    {
        $memoryPage = $this->memoryPageQueryRepository->getItem(new GetMemoryPageRequest($memoryPageId));
        $photos = $this->photoQueryRepository->getAllForMemoryPage($memoryPage);
        $memoryPage->setUpPhotos($photos);

        $this->transaction->beginTransaction();
        // Удалить комменты.
        $this->commentCommandRepository->deleteAllComments($memoryPage->getId());

        // Удалить периоды работы
        $this->workPeriodsCommandRepository->deleteAllWorkPeriods($memoryPage->getId());

        // Удалить страницу памяти
        $this->memoryPageCommandRepository->delete($memoryPage->getId());

        // Удалить связанные фотографии
        foreach ($photos as $photo) {
            $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
        }

        $this->transaction->commit();
    }
}
