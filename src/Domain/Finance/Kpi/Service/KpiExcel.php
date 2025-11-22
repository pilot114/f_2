<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Service;

use App\Common\Helper\PeriodFormatter;
use App\Common\Service\Excel\BaseCommandExcelService;
use App\Domain\Finance\Kpi\Enum\KpiType;
use DateTimeImmutable;

class KpiExcel extends BaseCommandExcelService
{
    public function setName(DateTimeImmutable $monthDate, string $enterpriseName, KpiType $kpiType, bool $inRussia): self
    {
        $date = PeriodFormatter::getMonthlyPeriodTitle($monthDate);

        $title = "Начисление премии $enterpriseName - {$kpiType->getTitle()}, $date";
        if ($inRussia === false) {
            $title .= ', Страны';
        }
        $this->fileName = $title;
        return $this;
    }

    public function setContent(array $rows): self
    {
        $this
            ->setTabs(['Департамент'])
            ->eachItem($this->addKpi(...), $rows)
            ->setDefaultConfig()
        ;
        return $this;
    }

    protected function addKpi(array $item): array
    {
        return [
            'Фамилия'                    => $item['last_name'],
            'Имя'                        => $item['first_name'],
            'Отчество'                   => $item['middle_name'],
            'ЦФО. Контракт'              => $item['cfo_contract'],
            'ЦФО. Название'              => $item['cfo_name'],
            'KPI ежемесячный %'          => $item['kpi'],
            'KPI спринт %'               => $item['two_month_bonus'],
            'KPI квартальный %'          => $item['four_months_bonus'],
            'Предприятие'                => $item['enterprise_name'],
            'Оклад'                      => null,
            'KPI ежемесячный, к выплате' => null,
            'KPI спринт, к выплате'      => null,
            'KPI квартальный, к выплате' => null,
        ];
    }
}
