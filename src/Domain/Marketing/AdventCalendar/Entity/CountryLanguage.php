<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class CountryLanguage
{
    public function __construct(
        #[Column(name: 'id')] public string $lang,
        #[Column(name: 'main')] public bool $isMain,        // 0/1 - является ли язык основным для страны
        #[Column(name: 'name')] public string $name, // название языка
    ) {
    }
}
