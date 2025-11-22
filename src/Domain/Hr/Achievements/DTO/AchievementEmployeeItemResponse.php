<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

use DateTimeImmutable;

class AchievementEmployeeItemResponse
{
    public function __construct(
        public int $id,
        public DateTimeImmutable $receiveDate,
        public DateTimeImmutable $addedDate,
        public EmployeeResponse $employee,
        public AchievementResponse $achievement,
    ) {
    }
}
