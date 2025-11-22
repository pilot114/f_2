<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\DTO;

readonly class SaveOfferLanguageRequest
{
    public function __construct(
        public string $lang,
        public string $shortTitle,
        public string $typeName,
        public ?string $shortDescr = '',
        public ?string $fullDescription = '',
        public ?string $buttonText = '',
        public ?string $newsLink = '',
        public ?int $imageId = null,
        public ?string $imageUrl = null,
    ) {
    }
}
