<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

readonly class OfferLanguage
{
    public function __construct(
        #[Column(name: 'id')] public string $lang,
        #[Column(name: 'short_title')] public string $shortTitle,
        #[Column(name: 'type_name')] public string $typeName,
        #[Column(name: 'short_descr')] public ?string $shortDescr = '',

        #[Column(name: 'full_descr')] public ?string $fullDescription = '',
        #[Column(name: 'button_text')] public ?string $buttonText = '',
        #[Column(name: 'news_link')] public ?string $newsLink = '',
        #[Column(name: 'image_url')] public ?string $imageUrl = '',
        #[Column(name: 'image_id')] public ?string $imageId = '',
    ) {
    }
}
