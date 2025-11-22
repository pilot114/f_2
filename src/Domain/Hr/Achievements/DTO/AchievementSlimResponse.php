<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

readonly class AchievementSlimResponse
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
