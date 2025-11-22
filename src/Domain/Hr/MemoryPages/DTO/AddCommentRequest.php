<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AddCommentRequest
{
    public function __construct(
        public readonly int $memoryPageId,
        public readonly string $text,
        /** @var Photo[] $photos */
        #[Assert\Valid]
        #[Assert\Count(max: 10, maxMessage: 'Не может быть больше чем 10 фотографий.')]
        public readonly array $photos = []
    ) {
    }
}
