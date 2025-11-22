<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\DTO;

use App\Domain\Marketing\AdventCalendar\Entity\Language;
use Database\ORM\Attribute\Column;

class SaveMonthParamsRequest
{
    public function __construct(
        public readonly int $calendarId,
        /** @var array<Language> */
        #[Column(collectionOf: Language::class)] public readonly array $langs = [],
    ) {
    }
}
