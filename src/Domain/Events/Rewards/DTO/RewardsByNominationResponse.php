<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Nomination;

class RewardsByNominationResponse
{
    public static function build(Nomination $nomination, int $rewardId): array
    {
        return [
            'rewardName' => $nomination->getRewardNameByRewardId($rewardId),
            'nomination' => [
                'id'   => $nomination->id,
                'name' => $nomination->name,
            ],
            'program' => [
                'id'   => $nomination->getProgram()->id,
                'name' => $nomination->getProgram()->name,
            ],
            'rewards' => $nomination->getRewards(),
        ];
    }
}
