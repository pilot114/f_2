<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class StatusResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $date = '',
        public string $user = ''
    ) {
    }
}
