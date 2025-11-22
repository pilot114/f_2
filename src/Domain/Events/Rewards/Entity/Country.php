<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\CountryResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.country')]
class Country
{
    public function __construct(
        #[Column(name: 'id')] public readonly int $id,
        #[Column] public readonly string $name
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }

    public function toCountryResponse(): CountryResponse
    {
        return new CountryResponse(
            id: $this->id,
            name: $this->name
        );
    }
}
