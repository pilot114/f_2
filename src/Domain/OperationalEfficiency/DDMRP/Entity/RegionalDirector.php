<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\RegionDirectorResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp')]
class RegionalDirector
{
    public function __construct(
        #[Column(name: 'id')] public int           $id,
        #[Column(name: 'name')] private string     $name,
    ) {
    }

    public function toRegionDirectorResponse(): RegionDirectorResponse
    {
        return new RegionDirectorResponse(
            id: $this->id,
            name: $this->name,
        );
    }
}
