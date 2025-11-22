<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\OfferLanguage;

describe('OfferLanguage', function (): void {
    it('can be instantiated as readonly with all parameters', function (): void {
        $offerLang = new OfferLanguage(
            lang: 'ru',
            shortTitle: 'Специальное предложение',
            typeName: 'Акция',
            shortDescr: 'Краткое описание',
            fullDescription: 'Полное описание предложения',
            buttonText: 'Купить сейчас',
            newsLink: 'https://example.com/news',
            imageUrl: 'https://example.com/news'
        );

        expect($offerLang->lang)->toBe('ru')
            ->and($offerLang->shortTitle)->toBe('Специальное предложение')
            ->and($offerLang->typeName)->toBe('Акция')
            ->and($offerLang->shortDescr)->toBe('Краткое описание')
            ->and($offerLang->fullDescription)->toBe('Полное описание предложения')
            ->and($offerLang->buttonText)->toBe('Купить сейчас')
            ->and($offerLang->newsLink)->toBe('https://example.com/news')
            ->and($offerLang->imageUrl)->toBe('https://example.com/news');
    });

    it('can be instantiated with default nullable parameters', function (): void {
        $offerLang = new OfferLanguage(
            lang: 'en',
            shortTitle: 'Special Offer',
            typeName: 'Sale'
        );

        expect($offerLang->lang)->toBe('en')
            ->and($offerLang->shortTitle)->toBe('Special Offer')
            ->and($offerLang->typeName)->toBe('Sale')
            ->and($offerLang->shortDescr)->toBe('')
            ->and($offerLang->fullDescription)->toBe('')
            ->and($offerLang->buttonText)->toBe('')
            ->and($offerLang->newsLink)->toBe('')
            ->and($offerLang->imageUrl)->toBe('');
    });
});
