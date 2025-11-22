<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.kpi_accrued_history_metric')]
class KpiMetricHistory
{
    public function __construct(
        #[Column(name: 'id')] protected int $id,
        #[Column(name: 'metric_name')] protected string $name,

        #[Column(name: 'factual_value')] protected int $factualValue,
        #[Column(name: 'plan_value')] protected int $planValue,
        #[Column(name: 'weight')] protected float $weight,

        #[Column(name: 'calculation_description')] protected string $calculationDescription,
        #[Column(name: 'ranges_count')] protected int $rangesCount,
        #[Column(name: 'ranges_description')] protected string $rangesDescription,
        #[Column(name: 'unit_type_id')] protected UnitType $unitType,
        #[Column(name: 'payment_plan_type')] protected ?PaymentPlanType $planType = null,
    ) {
    }

    public function setData(int $plan, int $factual, float $weight): void
    {
        $this->planValue = $plan;
        $this->factualValue = $factual;
        $this->weight = $weight;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'factual'     => $this->factualValue,
            'plan'        => $this->planValue,
            'weight'      => $this->weight,
            'description' => $this->calculationDescription,
            'ranges'      => $this->getRanges(),
            'unitType'    => $this->unitType,
            'planType'    => $this->planType,
        ];
    }

    private function getRanges(): array
    {
        $parts = [];
        if (str_contains($this->rangesDescription, ';')) {
            $parts = explode(';', $this->rangesDescription);
            if ($this->rangesCount !== count($parts)) {
                throw new InvariantDomainException(
                    "Невалидный формат диапазонов (count: $this->rangesCount, description: $this->rangesDescription)"
                );
            }
        }

        $ranges = [];
        foreach ($parts as $part) {
            $part = explode('-', $part);
            if (count($part) !== 3) {
                throw new InvariantDomainException(
                    "Невалидный формат диапазонов (count: $this->rangesCount, description: $this->rangesDescription)"
                );
            }
            $ranges[] = [
                'startPercent' => (int) $part[0],
                'endPercent'   => (int) $part[1],
                'kpiPercent'   => (int) $part[2],
            ];
        }
        return $ranges;
    }
}
