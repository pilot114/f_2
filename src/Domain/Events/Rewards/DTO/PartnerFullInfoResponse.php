<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class PartnerFullInfoResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $contract,
        public CountryResponse $country,
        public bool $isFamily,
        public TicketResponse $tickets,
        public ?RangResponse $rang = null,
        public ?PartnerStatusResponse $status = null,
        public array $penalties = [],
        public array $rewardIssuanceState = []
    ) {
    }
}
