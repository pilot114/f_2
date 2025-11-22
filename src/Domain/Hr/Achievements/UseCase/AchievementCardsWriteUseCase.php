<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AchievementCardsWriteUseCase
{
    /**
     * @param CommandRepositoryInterface<Achievement> $commandRepository
     * @param QueryRepositoryInterface<Image> $imageRepository
     */
    public function __construct(
        private AchievementQueryRepository $readRepository,
        private CommandRepositoryInterface $commandRepository,
        private CategoryQueryRepository $categoryRepository,
        private QueryRepositoryInterface $imageRepository,
    ) {
    }

    public function deleteAchievement(int $id): bool
    {
        return $this->commandRepository->delete($id);
    }

    public function create(int $categoriesId, string $name, int $imageId, string $description): Achievement
    {
        $category = $this->categoryRepository->getById($categoriesId);
        if (!$category instanceof Category) {
            throw new NotFoundHttpException("Не найдена категория c id = $categoriesId");
        }

        if ($this->readRepository->nameExist($name)) {
            throw new ConflictHttpException("Уже существует категория с именем '$name'");
        }

        $card = new Achievement(
            id: Loader::ID_FOR_INSERT,
            name: $name,
            description: $description,
            image: $this->imageRepository->findOrFail($imageId, 'Не найдено изображение'),
            category: $category,
        );
        return $this->commandRepository->create($card);
    }

    public function update(int $id, int $categoriesId, string $name, int $imageId, string $description): Achievement
    {
        $card = $this->readRepository->getById($id);
        if (!$card instanceof Achievement) {
            throw new NotFoundHttpException("Не найдено достижение с id = $id");
        }
        $card->update(
            name: $name,
            description: $description,
            category: $this->categoryRepository->findOrFail($categoriesId, 'Не найдена категория'),
            image: $this->imageRepository->findOrFail($imageId, 'Не найдено изображение'),
        );
        return $this->commandRepository->update($card);
    }
}
