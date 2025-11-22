<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RewardByEventResponse
{
    public function __construct(
        public string $name,
        public string $winDate,
        public int $calculationResultId,
        public int $count
    ) {
    }
}
