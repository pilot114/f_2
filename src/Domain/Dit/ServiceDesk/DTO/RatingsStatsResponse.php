<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\DTO;

use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;

class RatingsStatsResponse
{
    private function __construct(
        public readonly int $year,
        public readonly ?float $averageRating,
        public readonly ?int $ratingsCount,
    ) {
    }

    public static function build(YearlyRatingsStats $yearlyStats): self
    {
        return new self(...$yearlyStats->toArray());
    }
}
