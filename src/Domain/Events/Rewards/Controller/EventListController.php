<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\EventsForVerificationFilterResponse;
use App\Domain\Events\Rewards\UseCase\GetEventsForVerificationFilterUseCase;

class EventListController
{
    public function __construct(
        private GetEventsForVerificationFilterUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.getEventsForVerificationFilter',
        'Список доступных мероприятий для фильтра',
        examples: [
            [
                'summary' => 'получить список доступных мероприятий для фильтра',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(
    ): EventsForVerificationFilterResponse {
        $events = $this->useCase->getList();

        return EventsForVerificationFilterResponse::build($events);
    }
}
