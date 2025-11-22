<?php

declare(strict_types=1);

namespace App\Domain\Portal\Dictionary\DTO;

readonly class EnumCaseResponse
{
    public function __construct(
        public string $name,
        public int|string $value,
        public ?string $title = null,
    ) {
    }
}
