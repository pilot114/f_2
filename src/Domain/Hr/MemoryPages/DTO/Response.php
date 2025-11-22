<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class Response
{
    public function __construct(
        public readonly int $id,
        #[Assert\Length(max: 200)]
        public readonly string $response
    ) {
    }
}
