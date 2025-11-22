<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;

class SetPartnerStatusRequest
{
    public function __construct(
        #[RpcParam('Id партнёра')]
        public readonly int $partnerId,
        #[RpcParam('Id мероприятия')]
        public readonly int $eventId,
        #[RpcParam('статус партнёра')]
        public readonly ?PartnerStatusType $partnerStatus,
        #[RpcParam('изменения состояния выдачи наград')]
        /** @var RewardIssuanceStateDto[] $rewardIssuanceStates */
        public readonly array $rewardIssuanceStates = []
    ) {
    }
}
