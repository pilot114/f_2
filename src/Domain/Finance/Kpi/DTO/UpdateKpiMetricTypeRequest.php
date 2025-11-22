<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

readonly class UpdateKpiMetricTypeRequest
{
    public function __construct(
        #[RpcParam('id метрики')] public int $id,
        #[RpcParam('название типа метрики')] public ?string $name,
        #[RpcParam('тип платежа')] public ?PaymentPlanType $planType,
        /** @var array<UpdateKpiRangeRequest> */
        #[RpcParam('Диапазоны (если есть)')] public array $ranges = [],
        #[RpcParam('Активен ли тип метрики')] public ?bool $isActive = true,
    ) {
    }
}
