<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp')]
class KpiResponsibleUser
{
    public function __construct(
        #[Column(name: 'id')]
        public int $id,
        #[Column(name: 'name')]
        public string $name = '',
        #[Column(name: 'response_name')]
        public string $responseName = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'responseName' => $this->responseName,
        ];
    }
}
