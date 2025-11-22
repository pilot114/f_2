<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;

class Offer
{
    public function __construct(
        #[Column(name: 'offer_background_image_id')] public readonly ?int $backgroundImageId = null,
        #[Column(name: 'offer_langs', collectionOf: OfferLanguage::class)] public array $langs = [],
        #[Column(name: 'id')] public readonly ?int $id = null,
    ) {
    }
}
