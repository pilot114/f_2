<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;

readonly class UpdateKpiRangeRequest
{
    public function __construct(
        #[RpcParam('начала диапазона')] public int $startPercent,
        #[RpcParam('конец диапазона')] public int $endPercent,
        #[RpcParam('процент KPI')] public int $kpiPercent,
    ) {
    }
}
