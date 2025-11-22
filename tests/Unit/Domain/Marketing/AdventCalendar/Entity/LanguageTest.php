<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\Language;

describe('Language', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $language = new Language(
            lang: 'ru',
            label: 'Русский язык',
            title: 'Russian Language'
        );

        expect($language->lang)->toBe('ru')
            ->and($language->label)->toBe('Русский язык')
            ->and($language->title)->toBe('Russian Language');
    });

    it('can be instantiated with nullable parameters', function (): void {
        $language = new Language(
            lang: 'en',
            label: null,
            title: null
        );

        expect($language->lang)->toBe('en');
        expect($language->label)->toBeNull();
        expect($language->title)->toBeNull();
    });
});
