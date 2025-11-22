<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Image;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Enumerable;

class GetImagesUseCase
{
    /**
     * @param QueryRepositoryInterface<Image> $repository
     */
    public function __construct(
        private QueryRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Enumerable<int, Image>
     */
    public function getImages(): Enumerable
    {
        return $this->repository->findAll();
    }
}
