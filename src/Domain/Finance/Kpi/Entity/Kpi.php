<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Common\Helper\PeriodFormatter;
use App\Domain\Finance\Kpi\Enum\KpiType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;
use DateTimeInterface;

#[Entity('tehno.kpi_accured_history')]
class Kpi
{
    private const NOT_FILLED = 888;
    private const NOT_ASSIGNED = 999;

    public function __construct(
        #[Column] private int $id,
        #[Column(name: 'dt')] private DateTimeImmutable $billingMonth,
        #[Column(name: 'kpi_type')] private KpiType $type,
        #[Column(name: 'kpi_value')] private ?int $value,
        #[Column(name: 'kpi_value_calc')] private ?int $valueCalculated = null,
        #[Column(name: 'is_sended')] private bool $isSent = false,
        #[Column(name: 'dt_send')] private ?DateTimeImmutable $sendDate = null,
        #[Column(name: 'history', collectionOf: KpiMetricHistory::class)]
        private array $metricHistory = [],
    ) {
        $realValue = $value >= 0 && $value <= 100;
        $fakeValue = in_array($value, [self::NOT_FILLED, self::NOT_ASSIGNED], true);

        if (!$realValue && !$fakeValue) {
            throw new InvariantDomainException('value должно быть в диапазоне 0-100 или иметь спецзначение (null, 888, 999)');
        }
        if ($billingMonth->format('j') !== '1') {
            throw new InvariantDomainException('billingMonth должна быть первым числом месяца');
        }
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    public function setValueCalculated(?int $valueCalculated): void
    {
        $this->valueCalculated = $valueCalculated;
    }

    public function getType(): KpiType
    {
        return $this->type;
    }

    public function getMetricHistory(): array
    {
        return array_values(
            array_map(static fn (KpiMetricHistory $x): array => $x->toArray(), $this->metricHistory)
        );
    }

    /**
     * @param list<KpiMetricHistory> $metrics
     */
    public function setMetricHistory(array $metrics): self
    {
        $this->metricHistory = $metrics;
        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    public function isFilled(): bool
    {
        return $this->value !== null && !in_array($this->value, [self::NOT_FILLED, self::NOT_ASSIGNED], true);
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function getBillingMonthIndex(): int
    {
        return (int) $this->billingMonth->format('n');
    }

    public function getBillingMonthString(): string
    {
        return $this->billingMonth->format(DateTimeInterface::ATOM);
    }

    public function toArray(int $empId, bool $withValueCalculated = false): array
    {
        $data = [
            'id'           => $this->id,
            'empId'        => $empId,
            'billingMonth' => $this->getBillingMonthString(),
            'type'         => $this->type,
            'periodTitle'  => $this->getPeriodTitle(),
            'value'        => $this->value,
            'valueTitle'   => $this->getKpiValueTitle(),
            'isSent'       => $this->isSent,
            'sendDate'     => $this->sendDate?->format(DateTimeInterface::ATOM),
            'isActual'     => $this->isActualPeriod(),
        ];

        if ($withValueCalculated) {
            $data['valueCalculated'] = $this->valueCalculated;
        }
        return $data;
    }

    public function isActualPeriod(): bool
    {
        return $this->billingMonth->modify('+1 month')->format('Y-m') === date('Y-m');
    }

    private function getPeriodTitle(): string
    {
        return match ($this->type) {
            KpiType::MONTHLY   => PeriodFormatter::getMonthlyPeriodTitle($this->billingMonth),
            KpiType::BIMONTHLY => PeriodFormatter::getBimonthlyPeriodTitle($this->billingMonth),
            KpiType::QUARTERLY => PeriodFormatter::getQuarterlyPeriodTitle($this->billingMonth),
        };
    }

    private function getKpiValueTitle(): string
    {
        return match ($this->value) {
            null               => '—',
            self::NOT_FILLED   => 'Не заполняется',
            self::NOT_ASSIGNED => 'Не назначен',
            default            => (string) $this->value
        };
    }
}
