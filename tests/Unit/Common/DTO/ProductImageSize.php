<?php

declare(strict_types=1);

use App\Common\DTO\ProductImageSize;

describe('ProductImageSize', function (): void {
    it('has correct values', function (): void {
        expect(ProductImageSize::NOSIZE->value)->toBe('')
            ->and(ProductImageSize::SIZE60->value)->toBe('small')
            ->and(ProductImageSize::SIZE150->value)->toBe('w150')
            ->and(ProductImageSize::SIZE220->value)->toBe('medium_small')
            ->and(ProductImageSize::SIZE300->value)->toBe('medium');
    });

    it('identifies NOSIZE correctly', function (): void {
        expect(ProductImageSize::NOSIZE->isNoSize())->toBeTrue()
            ->and(ProductImageSize::SIZE60->isNoSize())->toBeFalse()
            ->and(ProductImageSize::SIZE150->isNoSize())->toBeFalse();
    });
});
