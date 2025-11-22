<?php

declare(strict_types=1);

namespace App\System;

use App\Domain\Portal\Security\Entity\SecurityUser;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\UserDataBag;
use Symfony\Bundle\SecurityBundle\Security;

class SentryUserResolver
{
    public function __construct(
        protected Security $security,
    ) {
    }

    public function __invoke(Event $event, ?EventHint $hint): ?Event
    {
        /** @var ?SecurityUser $currentUser */
        $currentUser = $this->security->getUser();
        $dataBag = new UserDataBag(
            id: $currentUser?->getUserIdentifier(),
            username: $currentUser?->name,
        );
        $event->setUser($dataBag);

        return $event;
    }
}
