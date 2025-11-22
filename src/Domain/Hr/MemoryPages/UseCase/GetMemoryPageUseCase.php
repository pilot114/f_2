<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;

class GetMemoryPageUseCase
{
    public function __construct(
        private MemoryPageQueryRepository $repository,
        private MemoryPagePhotoQueryRepository $photoRepository,
    ) {
    }

    public function getItem(GetMemoryPageRequest $request): MemoryPage
    {
        $memoryPage = $this->repository->getItem($request);
        $photos = $this->photoRepository->getAllForMemoryPage($memoryPage);
        $memoryPage->setUpPhotos($photos);
        $memoryPage->sortComments();
        $memoryPage->sortWorkPeriods();

        return $memoryPage;
    }
}
