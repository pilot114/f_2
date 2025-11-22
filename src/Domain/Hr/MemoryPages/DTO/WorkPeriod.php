<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class WorkPeriod
{
    public function __construct(
        #[Assert\When(
            expression: 'this.id === null || (this.id !== null && this.toDelete === false)',
            constraints: [
                new Assert\NotNull(message: 'startDate должна быть задана'),
            ]
        )]
        public readonly ?DateTimeImmutable $startDate = null,
        #[Assert\When(
            expression: 'this.id === null || (this.id !== null && this.toDelete === false)',
            constraints: [
                new Assert\NotNull(message: 'endDate должно быть задана'),
            ]
        )]
        public readonly ?DateTimeImmutable $endDate = null,
        #[Assert\When(
            expression: 'this.id === null || (this.id !== null && this.toDelete === false)',
            constraints: [
                new Assert\NotNull(message: 'responseId должно быть задано'),
            ]
        )]
        public readonly ?int $responseId = null,
        public readonly ?string $achievements = null,
        public readonly ?int $id = null,
        public readonly bool $toDelete = false
    ) {
    }
}
