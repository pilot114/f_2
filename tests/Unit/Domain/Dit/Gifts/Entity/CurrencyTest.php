<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\Entity;

use App\Domain\Dit\Gifts\Entity\Currency;

it('creates currency with all fields', function (): void {
    $currency = new Currency(
        id: 123,
        logo: 'USD'
    );

    expect($currency->id)->toBe(123);
    expect($currency->logo)->toBe('USD');
});

it('converts to array correctly', function (): void {
    $currency = new Currency(
        id: 456,
        logo: 'EUR'
    );

    $array = $currency->toArray();

    expect($array)->toBe([
        'id'   => 456,
        'logo' => 'EUR',
    ]);
});
