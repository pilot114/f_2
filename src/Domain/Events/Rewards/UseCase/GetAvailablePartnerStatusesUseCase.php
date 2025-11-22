<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\DTO\PartnerStatusTypeResponse;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use Illuminate\Support\Enumerable;

class GetAvailablePartnerStatusesUseCase
{
    /** @return Enumerable<int<0, max>, PartnerStatusTypeResponse> */
    public function getList(): Enumerable
    {
        $items = [];

        foreach (PartnerStatusType::cases() as $statusType) {
            $items[] = new PartnerStatusTypeResponse(
                id: $statusType->value,
                name: $statusType::getStatusName($statusType)
            );
        }

        return EnumerableWithTotal::build($items);
    }
}
