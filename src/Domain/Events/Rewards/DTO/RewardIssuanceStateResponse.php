<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RewardIssuanceStateResponse
{
    public function __construct(
        public int $id,
        public ProgramResponse $program,
        public NominationResponse $nomination,
        public CalculationResultResponse $calculationResult,
        public int $rewardsCount,
        public RewardResponse $reward,
        public StatusResponse $status,
        public string $winDate,
        public ?string $rewardDate,
        public ?UserResponse $rewardedByUser,
        public ?string $note,
        public bool $deleted
    ) {
    }
}
