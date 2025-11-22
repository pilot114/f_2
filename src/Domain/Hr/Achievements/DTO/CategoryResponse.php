<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

readonly class CategoryResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isPersonal,
        public bool $isHidden,
        public ?ColorResponse $color = null,
        public array $achievements = [],
    ) {
    }
}
