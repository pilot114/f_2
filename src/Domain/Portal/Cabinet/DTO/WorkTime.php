<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use DateTimeImmutable;

class WorkTime
{
    public function __construct(
        public readonly DateTimeImmutable $start,
        public readonly DateTimeImmutable $end,
        public readonly WorkTimeTimeZone $timeZone
    ) {
    }
}
