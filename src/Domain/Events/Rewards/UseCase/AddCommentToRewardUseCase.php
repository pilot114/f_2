<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Repository\RewardCommandRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;

class AddCommentToRewardUseCase
{
    public function __construct(
        private RewardQueryRepository $readReward,
        private RewardCommandRepository $writeReward,
    ) {
    }

    public function addCommentToReward(int $rewardId, ?string $comment = null): void
    {
        $reward = $this->readReward->getOne($rewardId);
        $reward->setComment($comment);
        $this->writeReward->addCommentToReward($reward);
    }
}
