<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class NominationResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $date
    ) {
    }
}
