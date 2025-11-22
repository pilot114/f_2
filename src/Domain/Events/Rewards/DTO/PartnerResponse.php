<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class PartnerResponse
{
    public function __construct(
        public int $id,
        public ContractResponse $contract,
        public string $country,
        public bool $deleted,
        public string $program,
        public NominationResponse $nomination,
        /** @var AwardsResponse[] $awards */
        public array $awards,
        /** @var PenaltyResponse[] $penalties */
        public array $penalties
    ) {
    }
}
