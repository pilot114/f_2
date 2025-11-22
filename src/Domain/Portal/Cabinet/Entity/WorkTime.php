<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: WorkTime::TABLE, sequenceName: WorkTime::SEQUENCE)]
class WorkTime
{
    public const SEQUENCE = 'TEST.CP_EMP_WORKTIME_SQ';
    public const TABLE = 'TEST.CP_EMP_WORKTIME';

    public function __construct(
        #[Column] private int                                   $id,
        #[Column(name: 'emp_id')] public readonly int           $userId,
        #[Column(name: 'time_start')] private DateTimeImmutable $start,
        #[Column(name: 'time_end')] private DateTimeImmutable   $end,
        #[Column(name: 'timezone')] private WorkTimeTimeZone    $timeZone,
    ) {
    }

    public function toArray(): array
    {
        return [
            'start'    => $this->start->format(DateTimeImmutable::ATOM),
            'end'      => $this->end->format(DateTimeImmutable::ATOM),
            'timeZone' => $this->timeZone,
        ];
    }

    public function updateTime(DateTimeImmutable $start, DateTimeImmutable $end, WorkTimeTimeZone $timeZone): void
    {
        $this->start = $start;
        $this->end = $end;
        $this->timeZone = $timeZone;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function getTimeZone(): WorkTimeTimeZone
    {
        return $this->timeZone;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
