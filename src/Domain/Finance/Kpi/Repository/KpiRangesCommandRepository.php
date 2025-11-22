<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiMetricRange;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<KpiMetricRange>
 */
class KpiRangesCommandRepository extends CommandRepository
{
    protected string $entityName = KpiMetricRange::class;

    public function addRange(KpiMetricRange $range): KpiMetricRange
    {
        $raw = $this->normalize($range);
        $result = $this->conn->procedure('tehno.pkpi.kpi_metric_ranges_add', [
            'pMetric_type_id' => $raw['kpi_metric_type_id'],
            'pStart_value'    => $raw['start_value'],
            'pEnd_value'      => $raw['end_value'],
            'pPercent'        => $raw['kpi_percent'],
            'pId'             => null,
        ], [
            'pId' => [ParamMode::OUT, ParamType::INTEGER],
        ]);
        $raw['id'] = $result['pId'];
        return $this->denormalize($raw);
    }

    public function deleteRangesByMetricTypeId(int $metricTypeId): void
    {
        $this->conn->procedure('tehno.pkpi.kpi_metric_ranges_delete_all', [
            'pMetric_type_id' => $metricTypeId,
        ]);
    }
}
