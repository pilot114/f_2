<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;

readonly class UpdateKpiMetricRequest
{
    public function __construct(
        #[RpcParam('id метрики')] public int                          $id,
        #[RpcParam('название метрики')] public ?string                $name = null,
        #[RpcParam('тип метрики')] public ?KpiType                    $kpiType = null,
        #[RpcParam('тип расчета метрики')] public ?KpiCalculationType $calculationType = null,
        #[RpcParam('описание типа расчета метрики')] public ?string   $calculationTypeDescription = null,
        #[RpcParam('единица измерения')] public ?UnitType             $unitType = null,
        #[RpcParam('id группы метрики')] public ?int                  $groupId = null,
        #[RpcParam('id типа метрики')] public ?int                    $metricTypeId = null,
        #[RpcParam('метрика включена')] public ?bool                  $isActive = null,
        /** @var array<CreateKpiMetricDepartmentRequest> */
        #[RpcParam('привязка департамента и должности')] public array $metricDepartments = [],
    ) {
    }
}
