<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\NominationResponse;
use App\Domain\Events\Rewards\DTO\NominationWithRewardsResponse;
use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.pd_nominations')]
class Nomination
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
        #[Column] private Program $program,
        /** @var Reward[] */
        #[Column(collectionOf: Reward::class)] private array $rewards = []
    ) {
    }

    public function toNominationResponse(string $date): NominationResponse
    {
        return new NominationResponse(
            id: $this->id,
            name: $this->name,
            date: $date
        );
    }

    public function getProgram(): Program
    {
        return $this->program;
    }

    /** @return RewardFullResponse[] */
    public function getRewards(): array
    {
        return array_values(array_map(fn (Reward $reward): RewardFullResponse => $reward->toRewardFullResponse(), $this->rewards));
    }

    public function getRewardNameByRewardId(int $rewardId): ?string
    {
        return isset($this->rewards[$rewardId]) ? $this->rewards[$rewardId]->name : null;
    }

    public function toNominationWithRewardsResponse(): NominationWithRewardsResponse
    {
        return new NominationWithRewardsResponse(
            id: $this->id,
            name: $this->name,
            rewards_count: count($this->rewards),
            rewards: $this->getRewards()
        );
    }

    public function setRewards(array $rewards): void
    {
        $this->rewards = $rewards;
    }
}
