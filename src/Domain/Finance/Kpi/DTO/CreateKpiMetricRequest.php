<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;

readonly class CreateKpiMetricRequest
{
    public function __construct(
        #[RpcParam('название метрики')] public string                 $name,
        #[RpcParam('тип метрики')] public KpiType                     $kpiType,
        #[RpcParam('тип расчета метрики')] public KpiCalculationType  $calculationType,
        #[RpcParam('описание типа расчета метрики')] public string    $calculationTypeDescription,
        #[RpcParam('единица измерения')] public UnitType              $unitType,
        #[RpcParam('id группы метрики')] public int                   $groupId,
        #[RpcParam('id типа метрики')] public int                     $metricTypeId,
        /** @var array<CreateKpiMetricDepartmentRequest> */
        #[RpcParam('привязка департамента и должности')] public array $metricDepartments = [],
    ) {
    }
}
