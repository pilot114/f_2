<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\DTO;

use Database\ORM\Attribute\Column;

readonly class SaveOfferRequest
{
    public function __construct(
        public int $calendarId,
        public int $active,
        public int $bkImageId,
        public ?int $offerId = null,
        /** @var array<SaveOfferLanguageRequest> */
        #[Column(collectionOf: SaveOfferLanguageRequest::class)] public array $langs = [],
    ) {
    }
}
