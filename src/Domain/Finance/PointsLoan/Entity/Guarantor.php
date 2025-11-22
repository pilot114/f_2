<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'net.employee')]
readonly class Guarantor
{
    public function __construct(
        #[Column(name: 'id')] public int          $id,
        #[Column(name: 'contract')] public string $contract,
        #[Column(name: 'd_end')] public ?DateTimeImmutable $closedAt = null,
    ) {
    }

    public function isActive(): bool
    {
        return !($this->closedAt instanceof DateTimeImmutable);
    }
}
