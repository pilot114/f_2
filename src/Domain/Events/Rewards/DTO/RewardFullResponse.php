<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RewardFullResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $commentary,
        /** @var RewardStatusResponse[] $statuses */
        public readonly array $statuses,
        public ?RewardTypeResponse $type
    ) {
    }
}
