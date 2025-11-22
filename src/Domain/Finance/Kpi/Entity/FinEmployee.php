<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

/**
 * Эта сущность нужна, чтобы делать преобразование
 * cp_emp.id <=> tehno.finemployee.id
 */
#[Entity('tehno.finemployee')]
class FinEmployee
{
    public function __construct(
        #[Column] private int $id,
        #[Column(name: 'cp_id')] private int $cpId,
        #[Column(name: 'first_name')] private string $firstName,
        #[Column(name: 'middle_name')] private string $middleName,
        #[Column(name: 'last_name')] private string $lastName,
    ) {
    }

    public function getFinEmpId(): int
    {
        return $this->id;
    }

    public function getCpId(): int
    {
        return $this->cpId;
    }

    public function getFio(): string
    {
        return "$this->lastName $this->firstName $this->middleName";
    }
}
