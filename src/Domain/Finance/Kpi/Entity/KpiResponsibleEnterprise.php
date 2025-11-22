<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.finclient')]
class KpiResponsibleEnterprise
{
    public function __construct(
        #[Column(name: 'id')]
        public int $id,
        #[Column(name: 'name')]
        public string $name = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
