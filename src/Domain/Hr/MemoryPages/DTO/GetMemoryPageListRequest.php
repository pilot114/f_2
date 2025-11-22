<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

class GetMemoryPageListRequest
{
    public function __construct(
        public readonly ?string $search = null,
    ) {
    }
}
