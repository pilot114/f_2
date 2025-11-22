<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use App\Domain\Marketing\AdventCalendar\Entity\OfferLanguage;

describe('Offer', function (): void {
    it('can be instantiated with default parameters', function (): void {
        $offer = new Offer();

        expect($offer->backgroundImageId)->toBeNull()
            ->and($offer->langs)->toBe([])
            ->and($offer->id)->toBeNull();
    });

    it('can be instantiated with all parameters', function (): void {
        $langRu = new OfferLanguage('ru', 'Предложение', 'Акция');
        $langEn = new OfferLanguage('en', 'Offer', 'Sale');

        $offer = new Offer(
            backgroundImageId: 5,
            langs: [$langRu, $langEn],
            id: 10
        );

        expect($offer->backgroundImageId)->toBe(5)
            ->and($offer->langs)->toHaveCount(2)
            ->and($offer->id)->toBe(10);
    });

    it('has readonly properties that cannot be modified after instantiation', function (): void {
        $offer = new Offer(backgroundImageId: 1, id: 2);

        expect($offer->backgroundImageId)->toBe(1)
            ->and($offer->id)->toBe(2);
    });
});
