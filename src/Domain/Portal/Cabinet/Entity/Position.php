<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_emp')]
class Position
{
    public function __construct(
        #[Column(name: 'name')] private ?string $name = null,
        #[Column(name: 'description')] private ?string $description = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
        ];
    }
}
