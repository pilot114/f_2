<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Cabinet\Entity\Congratulation;
use Illuminate\Support\Enumerable;

class GetCongratulationsResponse
{
    private function __construct(
        public array $items,
        public int $total
    ) {
    }

    /**
     * @param Enumerable<int, Congratulation> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->groupBy(fn (Congratulation $congratulation): string => $congratulation->getYear()->format('Y'))
            ->map(fn ($group) => $group
                ->map(fn (Congratulation $congratulation): array => $congratulation->toArray())
                ->values()
                ->all()
            )
            ->all();

        return new self(
            $items,
            $entities->getTotal()
        );
    }
}
