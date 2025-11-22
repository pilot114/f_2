<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\CountryResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'tehno.country')]
class Country
{
    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name
    ) {
    }

    public function toCountryResponse(): CountryResponse
    {
        return new CountryResponse(
            id: $this->id,
            name: $this->name
        );
    }
}
