<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Enum;

enum GroupType: int
{
    case GROUP = 1;
    case CATEGORY = 2;
}
