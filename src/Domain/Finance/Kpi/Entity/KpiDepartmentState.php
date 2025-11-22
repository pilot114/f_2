<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_depart_state')]
class KpiDepartmentState
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] private string $name,
        #[Column(name: 'is_boss')] private bool $isBoss,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isBoss(): bool
    {
        return $this->isBoss;
    }
}
