<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use App\Domain\Finance\PointsLoan\Enum\ViolationType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'tehno.blacklist')]
readonly class Violation
{
    public function __construct(
        #[Column(name: 'id')] public int             $id,
        #[Column(name: 'type')] public ViolationType $type,
        #[Column(name: 'commentary')] public string  $commentary
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'commentary' => $this->commentary,
        ];
    }
}
