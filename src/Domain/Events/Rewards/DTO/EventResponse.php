<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use DateTimeImmutable;

class EventResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $countryName,
        public string $cityName,
        public DateTimeImmutable $start,
        public DateTimeImmutable $end
    ) {
    }
}
