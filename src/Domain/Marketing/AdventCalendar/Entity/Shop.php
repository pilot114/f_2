<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class Shop
{
    public function __construct(
        #[Column(name: 'shop')] public ?string $code,
        #[Column(name: 'name')] public ?string $name,
        #[Column(name: 'nameRu')] public ?string $nameRu,
    ) {
    }
}
