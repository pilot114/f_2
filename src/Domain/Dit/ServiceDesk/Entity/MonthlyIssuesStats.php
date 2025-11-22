<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\Entity;

use DateTimeImmutable;

class MonthlyIssuesStats
{
    public function __construct(
        public readonly DateTimeImmutable $month,
        public readonly int $createdIssues,
        public readonly int $resolvedIssues,
    ) {
    }

    public function toArray(): array
    {
        return [
            'month'          => $this->month->format(DateTimeImmutable::ATOM),
            'createdIssues'  => $this->createdIssues,
            'resolvedIssues' => $this->resolvedIssues,
        ];
    }
}
