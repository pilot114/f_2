<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class Photo
{
    public function __construct(
        public readonly ?int $id = null,
        #[Assert\When(
            expression: 'this.id === null || (this.id !== null && this.toDelete === false)',
            constraints: [
                new Assert\NotNull(message: 'base64 должно быть задано'),
            ]
        )]
        public readonly ?string $base64 = null,
        public readonly bool $toDelete = false
    ) {
    }
}
