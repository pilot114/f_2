<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use App\Domain\Marketing\AdventCalendar\Entity\MonthOffer;
use App\Domain\Marketing\AdventCalendar\Entity\MonthParams;
use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;

describe('AdventItem', function (): void {
    it('can be instantiated with required parameters', function (): void {
        $params = new MonthParams(2024, 12, 'December', []);

        $item = new AdventItem(
            id: 1,
            params: $params,
            calendarId: 1,
            products: [],
            offers: []
        );

        expect($item->id)->toBe(1)
            ->and($item->params)->toBe($params)
            ->and($item->products)->toBe([])
            ->and($item->offers)->toBe([]);
    });

    it('can be instantiated with products and offers', function (): void {
        $params = new MonthParams(2024, 12, 'December', []);
        $product = new MonthProduct(1, 'PROD001', 'Test Product', null);
        $offer = new MonthOffer(1, []);

        $item = new AdventItem(
            id: 1,
            params: $params,
            calendarId: 1,
            products: [$product],
            offers: [$offer]
        );

        expect($item->products)->toHaveCount(1)
            ->and($item->offers)->toHaveCount(1)
            ->and($item->products[0])->toBe($product)
            ->and($item->offers[0])->toBe($offer);
    });
});
