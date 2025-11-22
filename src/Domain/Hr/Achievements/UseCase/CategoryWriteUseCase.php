<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryWriteUseCase
{
    /**
     * @param CommandRepositoryInterface<Category> $repository
     * @param QueryRepositoryInterface<Category> $readRepository
     * @param QueryRepositoryInterface<Color> $colorRepository
     */
    public function __construct(
        private CommandRepositoryInterface $repository,
        private QueryRepositoryInterface  $readRepository,
        private QueryRepositoryInterface  $colorRepository,
    ) {
    }

    public function create(string $name, int $colorId, bool $isPersonal, bool $isHidden): Category
    {
        $color = $this->colorRepository->findOrFail($colorId, 'Не найден цвет');

        $category = $this->readRepository->findOneBy([
            'name' => $name,
        ]);
        if ($category !== null) {
            throw new ConflictHttpException("Категория с названием $name уже существует");
        }

        $category = new Category(Loader::ID_FOR_INSERT, $name, (int) $isPersonal, (int) $isHidden, $color);
        return $this->repository->create($category);
    }

    public function update(int $id, string $name, int $colorId, bool $isPersonal, bool $isHidden): Category
    {
        $color = $this->colorRepository->findOrFail($colorId, 'Не найден цвет');

        $category = $this->readRepository->find($id);
        if (!$category instanceof Category) {
            throw new NotFoundHttpException("Не существует категории с id = $id");
        }
        $category->update($name, $color, $isPersonal, $isHidden);
        return $this->repository->update($category);
    }
}
