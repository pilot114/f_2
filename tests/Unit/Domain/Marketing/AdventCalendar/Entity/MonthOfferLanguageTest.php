<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\MonthOfferLanguage;

describe('MonthOfferLanguage', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $offerLang = new MonthOfferLanguage(
            lang: 'ru',
            imageUrl: 'https://example.com/offer.jpg',
            typeName: 'Специальное предложение',
            shortTitle: 'Скидка 50%',
            shortDescr: 'Большая скидка на все товары',
            isMain: true,
            name: 'Русский',
            isFull: true
        );

        expect($offerLang->lang)->toBe('ru');
        expect($offerLang->imageUrl)->toBe('https://example.com/offer.jpg');
        expect($offerLang->typeName)->toBe('Специальное предложение');
        expect($offerLang->shortTitle)->toBe('Скидка 50%');
        expect($offerLang->shortDescr)->toBe('Большая скидка на все товары');
        expect($offerLang->isMain)->toBeTrue();
        expect($offerLang->name)->toBe('Русский');
        expect($offerLang->isFull)->toBeTrue();
    });

    it('can be instantiated with nullable image and type', function (): void {
        $offerLang = new MonthOfferLanguage(
            lang: 'en',
            imageUrl: null,
            typeName: null,
            shortTitle: '50% Off',
            shortDescr: 'Big discount on all items',
            isMain: false,
            name: 'English',
            isFull: false
        );

        expect($offerLang->imageUrl)->toBeNull()
            ->and($offerLang->typeName)->toBeNull()
            ->and($offerLang->isFull)->toBeFalse();
    });
});
