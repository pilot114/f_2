<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class PenaltyResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $start,
        public ?string $end,
        public string $prim
    ) {
    }
}
