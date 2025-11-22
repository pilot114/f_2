<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

readonly class DepartmentResponse
{
    public function __construct(
        public ?string $name, // Название отдела
        public bool $hasKpi, // Признак 'Отображать в сервисе Управление начислением KPI'
        public ?string $bossName, // ФИО руководителя департамента должности
        public string $bossPositionName, // Название должности руководителя департамента
        public ?string $bossUserpic = null, // URL аватарки руководителя департамента должности
    ) {
    }
}
