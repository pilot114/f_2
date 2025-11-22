<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\MonthLanguage;

describe('MonthLanguage', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $monthLang = new MonthLanguage(
            lang: 'ru',
            title: 'Декабрь 2024',
            label: 'Дек',
            isMain: true
        );

        expect($monthLang->lang)->toBe('ru')
            ->and($monthLang->title)->toBe('Декабрь 2024')
            ->and($monthLang->label)->toBe('Дек')
            ->and($monthLang->isMain)->toBeTrue();
    });

    it('can be instantiated with nullable title and label', function (): void {
        $monthLang = new MonthLanguage(
            lang: 'en',
            title: null,
            label: null,
            isMain: false
        );

        expect($monthLang->lang)->toBe('en');
        expect($monthLang->title)->toBeNull();
        expect($monthLang->label)->toBeNull();
        expect($monthLang->isMain)->toBeFalse();
    });
});
