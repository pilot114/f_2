<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use Database\ORM\Attribute\Column;
use DateTimeImmutable;

class Registration
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column(name: 'partner_id')] public readonly int $partnerId,
        #[Column(name: 'ticket_reg_date')] public readonly DateTimeImmutable $registrationDate
    ) {
    }
}
