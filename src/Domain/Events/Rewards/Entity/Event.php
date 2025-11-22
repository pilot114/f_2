<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\EventResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

//TODO Выяснить название sequence
#[Entity(name: 'inet.celeb', sequenceName: 'not clear')]
class Event
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column(name: 'event_name')] private string $name,
        #[Column(name: 'country')] private Country $country,
        #[Column(name: 'city_name')] private string $cityName,
        #[Column(name: 'date_start')] private DateTimeImmutable $start,
        #[Column(name: 'date_end')] private DateTimeImmutable $end,
    ) {
    }

    public function toEventResponse(): EventResponse
    {
        return new EventResponse(
            id: $this->id,
            name: $this->name,
            countryName: $this->country->name,
            cityName: $this->cityName,
            start: $this->start,
            end: $this->end
        );
    }
}
