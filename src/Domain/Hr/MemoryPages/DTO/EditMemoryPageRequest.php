<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class EditMemoryPageRequest
{
    public function __construct(
        public readonly int $id,
        /** @var WorkPeriod[] $workPeriods */
        #[Assert\Valid]
        public readonly array $workPeriods = [],
        /** @var Photo[] $otherPhotos */
        #[Assert\Valid]
        public readonly array $otherPhotos = [],
        public readonly ?int $employeeId = null,
        public readonly ?DateTimeImmutable $birthDate = null,
        public readonly ?DateTimeImmutable $deathDate = null,
        #[Assert\Length(max: 2000)]
        public readonly ?string $obituary = null,
        public readonly ?string $obituaryFull = null,
        public readonly ?string $mainPhotoBase64 = null,
    ) {
    }
}
