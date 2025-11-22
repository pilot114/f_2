<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class MonthParams
{
    public function __construct(
        // Месяц/год и локализованное имя месяца
        #[Column(name: 'year')] public int $year,
        #[Column(name: 'month')] public int $month,
        #[Column(name: 'name')] public string $name,

        #[Column(name: 'lang', collectionOf: MonthLanguage::class)] public array $langs = [],
    ) {
    }
}
