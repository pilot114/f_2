<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use Illuminate\Support\Enumerable;

class AchievementCardsReadUseCase
{
    public function __construct(
        private AchievementQueryRepository $readRepository,
    ) {
    }

    /** @return Enumerable<int, Achievement> */
    public function getAchievementCards(): Enumerable
    {
        return $this->readRepository->getList();
    }

    public function getAchievementCardById(int $id): ?Achievement
    {
        return $this->readRepository->getById($id);
    }

    public function cardIsExist(string $name): bool
    {
        return $this->readRepository->nameExist($name);
    }
}
