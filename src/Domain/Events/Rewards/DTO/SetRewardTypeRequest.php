<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class SetRewardTypeRequest
{
    public function __construct(
        public readonly int $rewardId,
        public readonly ?int $typeId,
    ) {
    }
}
