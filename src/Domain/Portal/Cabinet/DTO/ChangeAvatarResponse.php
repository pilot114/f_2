<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Files\Entity\File;

class ChangeAvatarResponse
{
    private function __construct(
        public readonly int $userId,
        public readonly string $original,
        public readonly string $small,
        public readonly string $medium,
        public readonly string $large,
    ) {
    }

    public static function build(File $avatar): self
    {
        return new self($avatar->getUserId(), ...$avatar->getImageUrls());
    }
}
