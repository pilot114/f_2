<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Partner;
use Illuminate\Support\Enumerable;

class PartnersByContractsResponse
{
    private function __construct(
        public array $items
    ) {
    }

    /**
     * @param Enumerable<int, Partner> $activeEntities
     * @param Enumerable<int, Partner> $deletedEntities
     */
    public static function build(Enumerable $activeEntities, Enumerable $deletedEntities): self
    {
        $items = [];

        $items = array_merge($items, self::format($activeEntities));
        $items = array_merge($items, self::format($deletedEntities));

        return new self($items);
    }

    /**
     * @param Enumerable<int, Partner> $entities
     */
    private static function format(Enumerable $entities): array
    {
        $items = [];

        foreach ($entities as $partner) {
            $items = array_merge($items, $partner->toPartnerByContractResponse());
        }
        return $items;
    }
}
