<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use DateTimeImmutable;

readonly class EmployeeSearchKpiResponse
{
    public function __construct(
        public bool               $hasNoKpi, // Нет ни одного типа KPI
        public bool               $hasKpi, // Указан ежемесячный KPI
        public bool               $hasTwoMonthKpi, // Указан двухмесячный KPI
        public bool               $hasFourMonthKpi, // Указан квартальный KPI
        public ?DateTimeImmutable $kpiLastChangeDate, // дата начала действия текущей зарплаты (KPI зависит от неё)
        public bool               $hasSalaryUU, // Есть зарплата в УУ
    ) {
    }
}
