<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;
use Illuminate\Support\Enumerable;

class CategoriesReadUseCase
{
    public function __construct(
        private CategoryQueryRepository $repository,
    ) {
    }

    /** @return  Enumerable<int, Category>  */
    public function getAll(): Enumerable
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): ?Category
    {
        return $this->repository->getById($id);
    }

    public function categoryIsExist(string $name): bool
    {
        return $this->repository->findOneBy([
            'name' => $name,
        ]) !== null;
    }
}
