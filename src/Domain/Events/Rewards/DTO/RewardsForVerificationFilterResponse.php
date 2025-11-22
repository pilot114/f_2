<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Reward;
use Illuminate\Support\Enumerable;

class RewardsForVerificationFilterResponse
{
    private function __construct(
        public array $items
    ) {
    }

    /**
     * @param Enumerable<int, Reward> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Reward $reward): array => [
                'id'   => $reward->productId,
                'name' => $reward->name . ' (' . $reward->getNomination()->name . ')',
            ])
            ->values()
            ->all();

        return new self($items);
    }
}
