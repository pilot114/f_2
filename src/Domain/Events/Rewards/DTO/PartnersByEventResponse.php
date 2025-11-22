<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Partner;
use Illuminate\Support\Enumerable;

class PartnersByEventResponse
{
    private function __construct(
        /** @var PartnerByEventResponse[] $items */
        public array $items,
        public int $total = 0,
        public int $page = 1,
        public int $pages = 0,
        public int $perPage = 0,
    ) {
    }

    /**
     * @param Enumerable<int, Partner> $entities
     */
    public static function build(Enumerable $entities, int $total, int $page, int $perPage): self
    {
        $items = $entities
            ->map(fn (Partner $partner): PartnerByEventResponse => $partner->toPartnerByEventResponse())
            ->values()
            ->all();

        $pages = ($perPage > 0 && $total > 0) ? (int) ceil($total / $perPage) : 0;

        return new self($items, $total, $page, $pages, $perPage);
    }
}
