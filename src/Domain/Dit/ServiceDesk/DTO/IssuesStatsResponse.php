<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\DTO;

use App\Domain\Dit\ServiceDesk\Entity\MonthlyIssuesStats;

class IssuesStatsResponse
{
    private function __construct(
        public readonly array $items,
        public readonly int $total
    ) {
    }

    /**
     * @param array<MonthlyIssuesStats> $monthlyStats
     */
    public static function build(array $monthlyStats): self
    {
        $months = array_map(fn (MonthlyIssuesStats $stats): array => $stats->toArray(), $monthlyStats);

        return new self($months, count($months));
    }
}
