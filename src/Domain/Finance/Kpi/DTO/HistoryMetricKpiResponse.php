<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\Kpi;
use Illuminate\Support\Enumerable;

readonly class HistoryMetricKpiResponse
{
    private function __construct(
        public array $items,
        public int $total,
    ) {
    }

    /**
     * @param Enumerable<int, Kpi> $entities
     */
    public static function build(Enumerable $entities, int $empId): self
    {
        $items = $entities
            ->map(function (Kpi $kpi) use ($empId): array {
                $data = $kpi->toArray($empId);
                $data['metrics'] = $kpi->getMetricHistory();
                return $data;
            })
            ->values()
        ;

        return new self(
            $items->toArray(),
            $entities->getTotal()
        );
    }
}
