<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<KpiMetricType>
 */
class KpiMetricTypeCommandRepository extends CommandRepository
{
    protected string $entityName = KpiMetricType::class;

    public function createMetricType(KpiMetricType $metricType): KpiMetricType
    {
        $raw = $this->normalize($metricType);
        $result = $this->conn->procedure('tehno.pkpi.kpi_metric_type_add', [
            'pName'           => $raw['name'],
            'pPayment_type'   => $raw['payment_plan_type'],
            'oMetric_type_id' => null,
        ], [
            'oMetric_type_id' => [ParamMode::OUT, ParamType::INTEGER],
        ]);
        $raw['id'] = $result['oMetric_type_id'];
        return $this->denormalize($raw);
    }

    public function updateMetricType(KpiMetricType $metricType): KpiMetricType
    {
        $raw = $this->normalize($metricType);
        $this->conn->procedure('tehno.pkpi.kpi_metric_type_edit', [
            'pMetric_type_id' => $raw['id'],
            'pName'           => $raw['name'] ?? null,
            'pPayment_type'   => $raw['payment_plan_type'] ?? null,
            'pIs_active'      => $raw['is_active'] ?? null,
        ]);
        return $metricType;
    }
}
