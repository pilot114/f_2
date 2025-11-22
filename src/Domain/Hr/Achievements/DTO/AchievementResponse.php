<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

readonly class AchievementResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public null | CategoryResponse | CategoryWithoutAchievementsResponse $category,
        public ImageResponse $image,
        public int $userCount,
    ) {
    }
}
