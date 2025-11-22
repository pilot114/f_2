<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\Entity;

use App\Domain\Marketing\CustomerHistory\Entity\ProductCountry;

it('creates product country entity with correct values', function (): void {
    $cntr = 'RU';
    $nameRu = 'Россия';

    $productCountry = new ProductCountry($cntr, $nameRu);

    expect($productCountry->id)->toBe($cntr)
        ->and($productCountry->name)->toBe($nameRu);
});

it('has correct table constant', function (): void {
    expect(ProductCountry::TABLE)->toBe('test.nc_product_country_langs');
});
