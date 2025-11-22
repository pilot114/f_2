<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\PenaltyResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'not clear', sequenceName: 'not clear')]
class Penalty
{
    public function __construct(
        #[Column] public readonly int        $id,
        #[Column] public readonly string     $name,
        #[Column] public readonly string     $prim,
        #[Column] private DateTimeImmutable  $start,
        #[Column] private ?DateTimeImmutable $end = null,
    ) {
    }

    public function toPenaltyResponse(): PenaltyResponse
    {
        return new PenaltyResponse(
            id: $this->id,
            name: $this->name,
            start: $this->start->format(DateTimeImmutable::ATOM),
            end: $this->end?->format(DateTimeImmutable::ATOM),
            prim: $this->prim
        );
    }
}
