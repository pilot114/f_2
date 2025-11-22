<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class MonthOfferLanguage
{
    public function __construct(
        #[Column(name: 'id')] public string $lang,
        #[Column(name: 'image_url')] public ?string $imageUrl,
        #[Column(name: 'type_name')] public ?string $typeName,
        #[Column(name: 'short_title')] public string $shortTitle,
        #[Column(name: 'short_descr')] public string $shortDescr,
        #[Column(name: 'main')] public bool $isMain, // 0/1 - является ли язык основным для страны
        #[Column(name: 'name')] public string $name, // название языка
        #[Column(name: 'full_description')] public bool $isFull, // название языка
    ) {
    }
}
