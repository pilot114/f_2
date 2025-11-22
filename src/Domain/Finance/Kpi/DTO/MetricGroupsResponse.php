<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use Illuminate\Support\Enumerable;

readonly class MetricGroupsResponse
{
    private function __construct(
        public array $items,
        public int   $total,
    ) {
    }

    /**
     * @param Enumerable<int, KpiMetricGroup> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (KpiMetricGroup $x): array => $x->toArray())
            ->values()
            ->all()
        ;

        return new self(
            $items,
            $entities->getTotal(),
        );
    }
}
