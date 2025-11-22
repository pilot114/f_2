<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Enum\KpiType;
use Illuminate\Support\Enumerable;

readonly class HistoryResponse
{
    private function __construct(
        public array $items,
        public int $total,
        public ?array $responsible = null,
    ) {
    }

    /**
     * @param Enumerable<int, Kpi> $entities
     */
    public static function build(Enumerable $entities, int $empId, KpiResponsible $responsible): self
    {
        $items = $entities
            ->groupBy(fn (Kpi $kpi): string => $kpi->getBillingMonthString())
            ->map(function (Enumerable $monthKpi, string $key) use ($empId): array {
                $getKpiByType = fn (KpiType $type): ?Kpi => $monthKpi->first(fn ($x): bool => $x->getType() === $type);
                /** @var ?Kpi $first */
                $first = $monthKpi->first();

                return [
                    'billingMonth' => $key,
                    'isActual'     => $first && $first->isActualPeriod(),
                    'kpiMonthly'   => $getKpiByType(KpiType::MONTHLY)?->toArray($empId, withValueCalculated: true),
                    'kpiBimonthly' => $getKpiByType(KpiType::BIMONTHLY)?->toArray($empId, withValueCalculated: true),
                    'kpiQuarterly' => $getKpiByType(KpiType::QUARTERLY)?->toArray($empId, withValueCalculated: true),
                ];
            })
            ->values()
        ;

        return new self(
            $items->toArray(),
            $entities->getTotal(),
            $responsible->toArray(),
        );
    }
}
