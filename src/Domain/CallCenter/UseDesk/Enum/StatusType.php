<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Enum;

enum StatusType: string
{
    case NEW = 'new';
    case REOPENED = 'reopened';
    case CLOSED = 'closed';
}
