<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_RESPONSE')]
class Response
{
    public function __construct(
        #[Column] public readonly int                  $id,
        #[Column(name: 'name')] public readonly string $name,
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
