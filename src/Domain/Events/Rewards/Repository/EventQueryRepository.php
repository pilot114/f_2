<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Event;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Event>
 */
class EventQueryRepository extends QueryRepository
{
    protected string $entityName = Event::class;

    /** @return Enumerable<int, Event> */
    public function getEventsForVerificationFilter(): Enumerable
    {
        $sql = "
                select
                c.id,
                c.name event_name,
                cntr.id country_id,
                cntr.name country_name,
                city.name city_name,
                c.run_date date_start,
                c.end_date date_end
                 
                from inet.celeb c
                join inet.celeb_type ct on ct.id = c.typeid
                join sibvaleo.site_ruscity_city city on city.id = c.city_id
                join sibvaleo.site_ruscity_region region on region.id = city.parentid
                join sibvaleo.site_ruscity_okrug okrug on okrug.id = region.parentid
                join sibvaleo.site_ruscity_country cntr on cntr.id = okrug.parentid
                 
                where c.active = 'Y'
                and c.run_date >= (add_months (sysdate, -24))
                and (c.typeid in (1,2,3) or ct.parentid in (1,2,3))";

        return $this->query($sql);
    }

    public function getEventByIdFromAllowedList(int $eventId): Event
    {
        $availableEvents = $this->getEventsForVerificationFilter();
        $event = $availableEvents->filter(fn (Event $event): bool => $event->id === $eventId)->first();

        if ($event === null) {
            throw new EntityNotFoundDatabaseException("среди списка разрешенных не нашлось мероприятия с id = " . $eventId);
        }

        return $event;
    }
}
