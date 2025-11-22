<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp')]
class DeputyUser
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column(name: 'name')] private string $name,
        /** @var array<int, CpDepartment> */
        #[Column(name: 'departments', collectionOf: CpDepartment::class)] private array $departments = [],
        /** @var array<int, KpiDepartmentState> */
        #[Column(name: 'positions', collectionOf: KpiDepartmentState::class)] private array $positions = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'positionName'   => $this->getMainPositionName(),
            'departmentName' => $this->getMainDepartmentName(),
        ];
    }

    private function getMainPositionName(): string
    {
        $main = $this->positions[array_key_first($this->positions)];
        return $main->getName();
    }

    private function getMainDepartmentName(): string
    {
        $main = $this->departments[array_key_first($this->departments)];
        return $main->getName();
    }
}
