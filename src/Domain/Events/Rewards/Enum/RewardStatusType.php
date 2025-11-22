<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Enum;

enum RewardStatusType: int
{
    case ACTIVE = 1;
    case ARCHIVE = 2;
}
