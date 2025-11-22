<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\Entity;

use App\Domain\Marketing\CustomerHistory\Entity\Language;

it('creates language entity with correct values', function (): void {
    $id = 'ru';
    $name = 'Русский';

    $language = new Language($id, $name);

    expect($language->id)->toBe($id)
        ->and($language->name)->toBe($name);
});

it('has correct table constant', function (): void {
    expect(Language::TABLE)->toBe('test.ml_langs');
});
