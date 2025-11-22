<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class ProgramWithNominationsResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $nominations_count,
        /** @var NominationWithRewardsResponse[] $nominations */
        public readonly array $nominations
    ) {
    }
}
