<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class PartnerStatusesListResponse
{
    public function __construct(
        /** @var PartnerStatusTypeResponse[] */
        public array $items
    ) {
    }
}
