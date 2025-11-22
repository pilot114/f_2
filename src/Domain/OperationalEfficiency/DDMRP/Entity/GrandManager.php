<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\GrandManagerResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp')]
class GrandManager
{
    public function __construct(
        #[Column(name: 'id')] public int           $id,
        #[Column(name: 'name')] private string     $name,
    ) {
    }

    public function toGrandManagerResponse(): GrandManagerResponse
    {
        return new GrandManagerResponse(
            id: $this->id,
            name: $this->name,
        );
    }
}
