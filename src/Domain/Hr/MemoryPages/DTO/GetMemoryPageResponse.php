<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use App\Domain\Hr\MemoryPages\Entity\MemoryPage;

class GetMemoryPageResponse
{
    private function __construct(
        public readonly int $id,
        public readonly array $employee,
        public readonly string $birthDate,
        public readonly string $deathDate,
        public readonly string $createDate,
        public readonly string $obituary,
        public readonly string $obituaryFull,
        public readonly array $mainPhoto,
        public readonly array $otherPhotos,
        public readonly array $response,
        public readonly array $workPeriods,
        public readonly array $comments,
    ) {
    }

    public static function build(MemoryPage $memoryPage): self
    {
        return new self(...$memoryPage->toArray());
    }
}
