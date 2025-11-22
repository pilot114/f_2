<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;

class RewardIssuanceStateDto
{
    public function __construct(
        #[RpcParam('Id состояния выдачи награды')]
        public readonly int $id,
        #[RpcParam('статус состояния выдачи награды')]
        public readonly RewardIssuanceStateStatusType $status,
        #[RpcParam('Комментарий к состоянию выдачи')]
        public readonly ?string $comment = null,
    ) {
    }
}
