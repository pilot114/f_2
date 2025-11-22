<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Country;
use Illuminate\Support\Enumerable;

class CountryListResponse
{
    private function __construct(
        public array $items,
        public int $total
    ) {
    }

    /**
     * @param Enumerable<int, Country> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Country $country): array => [
                ...$country->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $entities->getTotal()
        );
    }
}
