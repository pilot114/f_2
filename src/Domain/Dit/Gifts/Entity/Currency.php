<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.currency')]
class Currency
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $logo
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'logo' => $this->logo,
        ];
    }
}
