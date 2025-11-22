<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RewardResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $commentary,
        /** @var RewardStatusResponse[] */
        public array $statuses
    ) {
    }
}
