<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\CountryLanguage;

describe('CountryLanguage', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $countryLang = new CountryLanguage(
            lang: 'ru',
            isMain: true,
            name: 'Русский'
        );

        expect($countryLang->lang)->toBe('ru')
            ->and($countryLang->isMain)->toBeTrue()
            ->and($countryLang->name)->toBe('Русский');
    });

    it('can be instantiated with non-main language', function (): void {
        $countryLang = new CountryLanguage(
            lang: 'en',
            isMain: false,
            name: 'English'
        );

        expect($countryLang->lang)->toBe('en');
        expect($countryLang->isMain)->toBeFalse();
        expect($countryLang->name)->toBe('English');
    });
});
