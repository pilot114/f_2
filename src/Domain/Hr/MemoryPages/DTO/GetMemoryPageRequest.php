<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

class GetMemoryPageRequest
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
