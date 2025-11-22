<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_emp')]
class Address
{
    public function __construct(
        #[Column] private ?string $city = null
    ) {
    }

    public function getCityName(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function toArray(): array
    {
        return [
            'city' => $this->getCityName(),
        ];
    }
}
