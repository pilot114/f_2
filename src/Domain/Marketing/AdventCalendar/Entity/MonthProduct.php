<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class MonthProduct
{
    public function __construct(
        #[Column(name: 'id')] public int $id,
        #[Column(name: 'code')] public string $code,
        #[Column(name: 'name')] public string $name,
    ) {
    }
}
