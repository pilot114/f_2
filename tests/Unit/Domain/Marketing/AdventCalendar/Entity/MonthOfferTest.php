<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\MonthOffer;
use App\Domain\Marketing\AdventCalendar\Entity\MonthOfferLanguage;

describe('MonthOffer', function (): void {
    it('can be instantiated with id and empty langs', function (): void {
        $offer = new MonthOffer(
            id: 1,
            langs: []
        );

        expect($offer->id)->toBe(1)
            ->and($offer->langs)->toBe([]);
    });

    it('can be instantiated with nullable id', function (): void {
        $offer = new MonthOffer(
            id: null,
            langs: []
        );

        expect($offer->id)->toBeNull();
    });

    it('can be instantiated with language versions', function (): void {
        $langRu = new MonthOfferLanguage('ru', null, null, 'Скидка', 'Описание', true, 'Русский', false);
        $langEn = new MonthOfferLanguage('en', null, null, 'Discount', 'Description', false, 'English', false);

        $offer = new MonthOffer(
            id: 1,
            langs: [$langRu, $langEn]
        );

        expect($offer->langs)->toHaveCount(2)
            ->and($offer->langs[0])->toBe($langRu)
            ->and($offer->langs[1])->toBe($langEn);
    });
});
