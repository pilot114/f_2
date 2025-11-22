<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

readonly class CreateKpiMetricTypeRequest
{
    public function __construct(
        #[RpcParam('название типа метрики')] public string $name,
        #[RpcParam('тип платежа')] public PaymentPlanType $planType,
        /** @var array<CreateKpiRangeRequest> */
        #[RpcParam('Диапазоны (если есть)')] public array $ranges = [],
    ) {
    }
}
