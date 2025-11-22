<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiMetricRange;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<KpiMetricRange>
 */
class KpiRangesQueryRepository extends QueryRepository
{
    protected string $entityName = KpiMetricRange::class;

    public function existInRange(int $start, int $end, int $metricTypeId, ?int $id = null): bool
    {
        $sql = <<<SQL
            select r.id id
            from tehno.kpi_metric_type_ranges r
            where
                r.start_value <= :end AND r.end_value >= :start
                and r.kpi_metric_type_id = :metricTypeId
            SQL;

        $items = iterator_to_array($this->conn->query($sql, [
            'start'        => $start,
            'end'          => $end,
            'metricTypeId' => $metricTypeId,
        ]));
        $ids = array_column($items, 'id');

        if ($id === null) {
            return $ids !== [];
        }

        // если передали id - не учитываем диапазон c этим id
        return count($ids) > 1 || ((int) $ids[0] !== $id);
    }
}
