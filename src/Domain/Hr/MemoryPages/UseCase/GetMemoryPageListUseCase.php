<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageListRequest;
use App\Domain\Hr\MemoryPages\Entity\MemoryPageListItem;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageListQueryRepository;
use Illuminate\Support\Enumerable;

class GetMemoryPageListUseCase
{
    public function __construct(
        private MemoryPageListQueryRepository $repository,
        private MemoryPagePhotoService $photoService
    ) {
    }

    /**
     * @return Enumerable<int, MemoryPageListItem>
     */
    public function getList(GetMemoryPageListRequest $request): Enumerable
    {
        $memoryPages = $this->repository->getList($request);
        foreach ($memoryPages as $memoryPage) {
            $mainPhoto = $this->photoService->getById($memoryPage->mainPhotoId);
            $memoryPage->setMainPhoto($mainPhoto);
        }

        return $memoryPages;
    }
}
