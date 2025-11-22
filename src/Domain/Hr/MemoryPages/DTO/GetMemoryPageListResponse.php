<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use App\Domain\Hr\MemoryPages\Entity\MemoryPageListItem;
use Illuminate\Support\Enumerable;

class GetMemoryPageListResponse
{
    private function __construct(
        public array $items,
        public int $total,
    ) {
    }

    /**
     * @param Enumerable<int, MemoryPageListItem> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (MemoryPageListItem $memoryPage): array => [
                ...$memoryPage->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $entities->getTotal()
        );
    }
}
