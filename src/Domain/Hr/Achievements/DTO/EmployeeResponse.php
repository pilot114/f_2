<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

readonly class EmployeeResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $positionName,
    ) {
    }
}
