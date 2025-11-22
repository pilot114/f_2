<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Enum;

use App\Domain\Events\Rewards\DTO\StatusResponse;

enum RewardIssuanceStateStatusType: int
{
    case REWARDED_FULL = 1;
    case NOT_REWARDED = 0;

    public static function getStatusName(self $statusType): string
    {
        return match ($statusType) {
            RewardIssuanceStateStatusType::REWARDED_FULL => 'Выдан полностью',
            RewardIssuanceStateStatusType::NOT_REWARDED  => 'Не выдано',
        };
    }

    public function toStatusResponse(): StatusResponse
    {
        return new StatusResponse(
            id: $this->value,
            name: self::getStatusName($this),
            date: ''
        );
    }
}
