<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class EditCommentRequest
{
    public function __construct(
        public readonly int $commentId,
        public readonly string $text,
        /** @var Photo[] $photos */
        #[Assert\Valid]
        public readonly array $photos = [],
    ) {
    }
}
