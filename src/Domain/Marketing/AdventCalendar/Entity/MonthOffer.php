<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class MonthOffer
{
    public function __construct(
        // Идентификатор оффера
        #[Column(name: 'id')] public ?int $id,
        // Несколько языковых версий оффера
        #[Column(name: 'langs', collectionOf: MonthOfferLanguage::class)] public array $langs = [],
    ) {
    }
}
