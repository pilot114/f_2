<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: WorkPeriod::TABLE, sequenceName: WorkPeriod::SEQUENCE)]
class WorkPeriod
{
    public const SEQUENCE = 'TEST.CP_MP_WORK_PERIODS_SQ';
    public const TABLE = 'TEST.CP_MP_WORK_PERIODS';

    public function __construct(
        #[Column] private int                                   $id,
        #[Column(name: 'personal_page_id')] public readonly int $memoryPageId,
        #[Column(name: 'start_date')] private DateTimeImmutable $startDate,
        #[Column(name: 'end_date')] private DateTimeImmutable   $endDate,
        #[Column(name: 'response_id')] private Response         $response,
        #[Column] private ?string                               $achievements = null,
    ) {
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getAchievements(): ?string
    {
        return $this->achievements;
    }

    public function setAchievements(?string $achievements): void
    {
        $this->achievements = $achievements;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'memoryPageId' => $this->memoryPageId,
            'startDate'    => $this->startDate->format(DateTimeImmutable::ATOM),
            'endDate'      => $this->endDate->format(DateTimeImmutable::ATOM),
            'response'     => $this->response,
            'achievements' => $this->achievements,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
