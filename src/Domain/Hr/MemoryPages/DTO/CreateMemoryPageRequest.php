<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMemoryPageRequest
{
    public function __construct(
        public readonly int $employeeId,
        public readonly DateTimeImmutable $birthDate,
        public readonly DateTimeImmutable $deathDate,
        #[Assert\Length(max: 2000)]
        public readonly string $obituary,
        public readonly string $obituaryFull,
        public readonly string $mainPhotoBase64,
        /** @var WorkPeriod[] $workPeriods */
        #[Assert\Count(min: 1, minMessage: 'Необходим хотя бы один рабочий период')]
        public readonly array $workPeriods,
        /** @var Photo[] $otherPhotos */
        #[Assert\Valid]
        #[Assert\Count(max: 10, maxMessage: 'Не может быть больше чем 10 дополнительных фотографий.')]
        public readonly array $otherPhotos = [],
    ) {
    }
}
