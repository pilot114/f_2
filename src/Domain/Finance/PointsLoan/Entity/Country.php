<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.country')]
readonly class Country
{
    public function __construct(
        #[Column(name: 'id')] public int      $id,
        #[Column(name: 'name')] public string $name,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => mb_ucfirst(mb_strtolower($this->name)),
        ];
    }
}
