<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class MonthLanguage
{
    public function __construct(
        #[Column(name: 'id')] public string $lang,
        #[Column(name: 'title')] public ?string $title,
        #[Column(name: 'label')] public ?string $label,
        #[Column(name: 'main')] public bool $isMain,
    ) {
    }
}
