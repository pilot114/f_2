<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\Shop;

describe('Shop', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $shop = new Shop(
            code: 'SHOP001',
            name: 'Test Shop',
            nameRu: 'Тестовый магазин'
        );

        expect($shop->code)->toBe('SHOP001')
            ->and($shop->name)->toBe('Test Shop')
            ->and($shop->nameRu)->toBe('Тестовый магазин');
    });

    it('can be instantiated with nullable parameters', function (): void {
        $shop = new Shop(
            code: null,
            name: null,
            nameRu: null
        );

        expect($shop->code)->toBeNull()
            ->and($shop->name)->toBeNull()
            ->and($shop->nameRu)->toBeNull();
    });
});
