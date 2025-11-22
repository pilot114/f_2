<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\DTO\FilterOption;

readonly class RewardTypeRequest
{
    public function __construct(
        public int|FilterOption $id,
        public string $name
    ) {
    }
}
