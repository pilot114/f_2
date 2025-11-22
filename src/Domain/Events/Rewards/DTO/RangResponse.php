<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class RangResponse
{
    public function __construct(
        public int $id,
        public string $rang,
        public string $name,
        public string $date
    ) {
    }
}
