<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RewardIssuanceStateStatusesListResponse
{
    public function __construct(
        /** @var RewardIssuanceStateStatusTypeResponse[] */
        public array $items
    ) {
    }
}
