<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.kpi_metric_type')]
class KpiMetricType
{
    public function __construct(
        #[Column] protected int $id,
        #[Column] protected string $name,
        #[Column(name: 'payment_plan_type')] protected ?PaymentPlanType $planType = null,
        /** @var array<int, KpiMetricRange> */
        #[Column(collectionOf: KpiMetricRange::class)] protected array $ranges = [],
        #[Column(name: 'is_active')] protected bool $isActive = true,
        #[Column(collectionOf: KpiMetric::class)] protected array $metrics = [],
    ) {
    }

    public function setRanges(array $ranges): self
    {
        $this->ranges = $ranges;
        return $this;
    }

    public function setName(string $name): KpiMetricType
    {
        $this->name = $name;
        return $this;
    }

    public function setIsActive(bool $isActive): KpiMetricType
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function setPlanType(PaymentPlanType $planType): KpiMetricType
    {
        $this->planType = $planType;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        /** @var PaymentPlanType $planType */
        $planType = $this->planType;

        $ranges = match ($planType) {
            PaymentPlanType::LINEAR => [], // 'Процент выполнения плана равен проценту премии KPI'
            PaymentPlanType::RANGES => array_map(
                fn (KpiMetricRange $x): array => [
                    ...$x->toArray(),
                    'title' => $x->getTitle(),
                ],
                $this->ranges
            ),
        };

        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'planType' => $planType,
            'ranges'   => array_values($ranges),
            'metrics'  => array_values(array_map(fn (KpiMetric $x): array => $x->toArray(false), $this->metrics)),
        ];
    }
}
