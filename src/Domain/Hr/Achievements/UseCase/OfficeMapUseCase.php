<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;

class OfficeMapUseCase
{
    public function __construct(
        private AchievementEmployeeItemQueryRepository $repository,
        private CategoryQueryRepository $categoryRepository,
    ) {
    }

    public function getUserInfo(int $userId): array
    {
        // получаем ачивки пользователя
        $achievements = $this->repository->getEmployeeAchievements($userId);
        $userAchievementsIds = $achievements
            ->map(static fn (AchievementEmployeeItem $x): int => $x->getAchievementId())
            ->toArray()
        ;
        $receivedMap = [];
        foreach ($achievements as $achievement) {
            $receivedMap[$achievement->getAchievementId()][] = $achievement->getReceived();
        }

        // получаем все категории и их ачивки
        $categories = $this->categoryRepository->getAll();

        $result = [];
        foreach ($categories as $category) {
            $item = $category->toCategoryOfficeMapResponse($userAchievementsIds, $receivedMap);
            // показываем только те категории, где есть выданные ачивки
            if (count($item->unlocked) > 0) {
                $result[] = $item;
            }
        }
        return $result;
    }
}
