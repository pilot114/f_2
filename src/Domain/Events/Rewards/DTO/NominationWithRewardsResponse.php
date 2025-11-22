<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class NominationWithRewardsResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $rewards_count,
        /** @var RewardFullResponse[] $rewards */
        public readonly array $rewards
    ) {
    }
}
