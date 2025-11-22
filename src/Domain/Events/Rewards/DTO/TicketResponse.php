<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class TicketResponse
{
    public function __construct(
        public int $count,
        /** @var string[] */
        public array $registrationDates
    ) {
    }
}
