<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateStatusTypeResponse;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use Illuminate\Support\Enumerable;

class GetAvailableRewardIssuanceStateStatusesUseCase
{
    /** @return Enumerable<int<0, max>, RewardIssuanceStateStatusTypeResponse> */
    public function getList(): Enumerable
    {
        $items = [];

        foreach (RewardIssuanceStateStatusType::cases() as $statusType) {
            $items[] = new RewardIssuanceStateStatusTypeResponse(
                id: $statusType->value,
                name: $statusType::getStatusName($statusType)
            );
        }

        return EnumerableWithTotal::build($items);
    }
}
