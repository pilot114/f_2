<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\DTO;

class CreateMonthRequest
{
    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly string $shop,
    ) {
    }
}
