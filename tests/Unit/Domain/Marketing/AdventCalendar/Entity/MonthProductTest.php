<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;

describe('MonthProduct', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $product = new MonthProduct(
            id: 1,
            code: 'PROD001',
            name: 'Test Product',
        );

        expect($product->id)->toBe(1)
            ->and($product->code)->toBe('PROD001')
            ->and($product->name)->toBe('Test Product');
    });

    it('can be instantiated with nullable image', function (): void {
        $product = new MonthProduct(
            id: 2,
            code: 'PROD002',
            name: 'Product without image',
        );

        expect($product->id)->toBe(2);
    });
});
