<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class PartnerStatusResponse
{
    public function __construct(
        public int $id,
        public StatusTypeResponse $statusType,
        public ?int $rewardsCount,
        public ?int $penaltiesCount
    ) {
    }
}
