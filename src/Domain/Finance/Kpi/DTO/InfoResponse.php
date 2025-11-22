<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Helper\DateHelper;
use App\Common\Helper\PeriodFormatter;
use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Enum\KpiType;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

readonly class InfoResponse
{
    private function __construct(
        public ?string $monthlyPeriod,
        public ?string $bimonthlyPeriod,
        public ?string $quarterlyPeriod,
        public ?string $fillingPeriod,
        public string $currentTime,
    ) {
    }

    /**
     * Информация об актуальном периоде начисления KPI и необходимых действиях
     *
     * @param Enumerable<int<0, max>, Kpi> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $now = new DateTimeImmutable();
        $prevMonth = $now->modify('-1 month');

        $prevMonthIndex = (int) $prevMonth->format('n');
        $needShowBimonthly = $prevMonthIndex % 2 === 0;
        $needShowQuarterly = $prevMonthIndex % 3 === 0;

        $hasMonthly = false;
        $hasBimonthly = false;
        $hasQuarterly = false;

        foreach ($entities as $kpi) {
            if ($kpi->getBillingMonthIndex() !== $prevMonthIndex) {
                continue;
            }
            match ($kpi->getType()) {
                KpiType::MONTHLY   => $hasMonthly = true,
                KpiType::BIMONTHLY => $hasBimonthly = true,
                KpiType::QUARTERLY => $hasQuarterly = true,
            };
        }

        $info = [
            'monthlyPeriod'   => null,
            'bimonthlyPeriod' => null,
            'quarterlyPeriod' => null,
            'fillingPeriod'   => null,
            'currentTime'     => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
        ];

        if ($hasMonthly) {
            $info['monthlyPeriod'] = PeriodFormatter::getMonthlyPeriodTitle($prevMonth);
        }
        if ($hasBimonthly && $needShowBimonthly) {
            $info['bimonthlyPeriod'] = PeriodFormatter::getBimonthlyPeriodTitle($prevMonth);
        }
        if ($hasQuarterly && $needShowQuarterly) {
            $info['quarterlyPeriod'] = PeriodFormatter::getQuarterlyPeriodTitle($prevMonth);
        }

        if ($info['monthlyPeriod'] || $info['bimonthlyPeriod'] || $info['quarterlyPeriod']) {
            $date = DateHelper::ruDateFormat($now, 'MMMM');
            $period = "с 1 до 5 $date";
            $info['fillingPeriod'] = $period;
        }

        return new self(...$info);
    }
}
