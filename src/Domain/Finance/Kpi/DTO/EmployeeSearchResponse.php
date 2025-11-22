<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

readonly class EmployeeSearchResponse
{
    public function __construct(
        public int                        $id, // id сотрудника
        public string                     $name, // ФИО
        public bool                       $isActive, // Активный или нет
        public bool                       $isTechnical, // Техническая учётка
        public bool                       $hasUuId, // Есть ли у сотрудника учетная запись в системе управленческого учета
        public ?EmployeeSearchKpiResponse $kpi,
        public ?PositionResponse          $position,
        public ?DepartmentResponse        $department,
        public ?string                    $userpic = null, // URL аватарки
    ) {
    }
}
