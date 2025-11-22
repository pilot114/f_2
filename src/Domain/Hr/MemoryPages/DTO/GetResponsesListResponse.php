<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use App\Domain\Hr\MemoryPages\Entity\Response;
use Illuminate\Support\Enumerable;

class GetResponsesListResponse
{
    private function __construct(
        public array $items,
        public int $total,
    ) {
    }

    /**
     * @param Enumerable<int, Response> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Response $response): array => [
                ...$response->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $entities->getTotal()
        );
    }
}
