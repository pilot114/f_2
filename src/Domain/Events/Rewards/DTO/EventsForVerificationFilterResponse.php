<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Event;
use Illuminate\Support\Enumerable;

class EventsForVerificationFilterResponse
{
    private function __construct(
        /** @var EventResponse[] $items */
        public array $items,
    ) {
    }

    /**
     * @param Enumerable<int, Event> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Event $event): EventResponse => $event->toEventResponse())
            ->values()
            ->all();

        return new self($items);
    }
}
