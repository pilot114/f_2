<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\Entity;

class YearlyRatingsStats
{
    public function __construct(
        public readonly int $year,
        public readonly ?float $averageRating,
        public readonly ?int $ratingsCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'year'          => $this->year,
            'averageRating' => $this->averageRating,
            'ratingsCount'  => $this->ratingsCount,
        ];
    }
}
