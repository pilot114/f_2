<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\RangResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'not clear', sequenceName: 'not clear')]
class Rang
{
    public function __construct(
        #[Column] private int $id,
        #[Column(name: 'current_rang')] private string $rang,
        #[Column(name: 'current_rang_name')] private string $name,
        #[Column(name: 'current_rang_date')] private DateTimeImmutable $date,
    ) {
    }

    public function toRangResponse(): RangResponse
    {
        return new RangResponse(
            id: $this->id,
            rang: $this->rang,
            name: $this->name,
            date: $this->date->format(DateTimeImmutable::ATOM)
        );
    }
}
