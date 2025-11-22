<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use Illuminate\Support\Enumerable;

readonly class BossesResponse
{
    private function __construct(
        public array $items,
        public int $total,
        public int $countActualEmptyKpi,
    ) {
    }

    /**
     * @param Enumerable<int, KpiEmployee> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $result = $entities
            ->map(fn (KpiEmployee $x): int => $x->countActualEmptyKpi())
            ->sum();
        $countActualEmptyKpi = is_int($result) ? $result : 0;

        $items = $entities
            ->map(fn (KpiEmployee $emp): array => [
                ...$emp->toArray(),
                ...$emp->getUserPics(),
                ...$emp->getActualKpiEachType(),
                ...$emp->getMainPosition(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $entities->getTotal(),
            $countActualEmptyKpi,
        );
    }
}
