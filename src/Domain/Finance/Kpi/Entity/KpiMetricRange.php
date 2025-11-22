<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.kpi_metric_type_ranges')]
class KpiMetricRange
{
    public function __construct(
        #[Column] protected int $id,
        #[Column(name: 'start_value')] protected int $startPercent,
        #[Column(name: 'end_value')] protected int $endPercent,
        #[Column(name: 'kpi_percent')] protected int $kpiPercent,
        #[Column(name: 'kpi_metric_type_id')] protected int $metricTypeId,
    ) {
    }

    public function getTitle(): string
    {
        return "Выполнение плана $this->startPercent%-$this->endPercent% KPI к выплате $this->kpiPercent%";
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'startPercent' => $this->startPercent,
            'endPercent'   => $this->endPercent,
            'kpiPercent'   => $this->kpiPercent,
        ];
    }
}
