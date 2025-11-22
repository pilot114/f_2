<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiMetricHistory;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\EntityTracker;
use Database\ORM\Identity;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<KpiMetricHistory>
 */
class KpiMetricHistoryQueryRepository extends QueryRepository
{
    protected string $entityName = KpiMetricHistory::class;

    public function findOrFail(int|string|Identity $id, string $message): KpiMetricHistory
    {
        $sql = <<<SQL
            select
                ------------------------------------------ привязка метрик к kpi
                hm.id                       id,
                hm.metric_name              metric_name,
                hm.plan_value               plan_type,
                hm.factual_value            factual_value,
                hm.weight                   weight,
                hm.calculation_description  calculation_description,
                hm.ranges_count             ranges_count,
                hm.ranges_description       ranges_description,
                hm.payment_plan_type        payment_plan_type,
                km.KPI_METRIC_UNIT_TYPE_ID  unit_type_id
            from tehno.kpi_accrued_history_metric hm
            LEFT JOIN tehno.kpi_metric km ON km.id = hm.KPI_METRIC_ID
            where hm.id = :id
        SQL;

        $item = $this->conn->query($sql, [
            'id' => $id,
        ])->current();

        if (!$item) {
            if ($id instanceof Identity) {
                $id = $id->toString();
            }
            throw new EntityNotFoundDatabaseException($message . " c id = $id");
        }
        return EntityTracker::set($this->denormalize($item));
    }
}
