<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;

class SetRewardIssuanceStateStatusRequest
{
    public function __construct(
        #[RpcParam('Id партнёра')]
        public readonly int $partnerId,
        #[RpcParam('изменения состояния выдачи наград')]
        /** @var RewardIssuanceStateDto[] $rewardIssuanceStates */
        public readonly array $rewardIssuanceStates = []
    ) {
    }
}
