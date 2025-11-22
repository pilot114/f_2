<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

class PinCommentRequest
{
    public function __construct(
        public readonly int $commentId,
        public readonly bool $isPinned,
    ) {
    }
}
