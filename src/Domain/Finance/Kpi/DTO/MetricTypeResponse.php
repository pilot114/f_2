<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

readonly class MetricTypeResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public PaymentPlanType $planType,
        public array $ranges = [],
        public array $metrics = [],
    ) {
    }
}
