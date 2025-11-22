<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\RewardTypeResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('NET.PD_REWARD_TYPES')]
class RewardType
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
    ) {
    }

    public function toRewardTypeResponse(): RewardTypeResponse
    {
        return new RewardTypeResponse(
            id: $this->id,
            name: $this->name
        );
    }
}
