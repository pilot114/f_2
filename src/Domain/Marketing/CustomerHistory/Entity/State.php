<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Entity;

use Database\ORM\Attribute\Column;

readonly class State
{
    public function __construct(
        #[Column(name: 'id')] public ?int $id,
        #[Column(name: 'name')] public ?string $name,
    ) {
    }
}
