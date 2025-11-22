<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\ProductImageSize;

it('has correct enum values', function (): void {
    // Assert
    expect(ProductImageSize::NOSIZE->value)->toBe('')
        ->and(ProductImageSize::SIZE60->value)->toBe('small')
        ->and(ProductImageSize::SIZE150->value)->toBe('w150')
        ->and(ProductImageSize::SIZE220->value)->toBe('medium_small')
        ->and(ProductImageSize::SIZE300->value)->toBe('medium');
});

it('identifies NOSIZE correctly', function (): void {
    // Act & Assert
    expect(ProductImageSize::NOSIZE->isNoSize())->toBeTrue()
        ->and(ProductImageSize::SIZE60->isNoSize())->toBeFalse()
        ->and(ProductImageSize::SIZE150->isNoSize())->toBeFalse()
        ->and(ProductImageSize::SIZE220->isNoSize())->toBeFalse()
        ->and(ProductImageSize::SIZE300->isNoSize())->toBeFalse();
});

it('can be created from value', function (string $value, ProductImageSize $expected): void {
    // Act
    $size = ProductImageSize::from($value);

    // Assert
    expect($size)->toBe($expected);
})->with([
    ['', ProductImageSize::NOSIZE],
    ['small', ProductImageSize::SIZE60],
    ['w150', ProductImageSize::SIZE150],
    ['medium_small', ProductImageSize::SIZE220],
    ['medium', ProductImageSize::SIZE300],
]);

it('has all expected cases', function (): void {
    // Act
    $cases = ProductImageSize::cases();

    // Assert
    expect($cases)->toHaveCount(5)
        ->and($cases)->toContain(
            ProductImageSize::NOSIZE,
            ProductImageSize::SIZE60,
            ProductImageSize::SIZE150,
            ProductImageSize::SIZE220,
            ProductImageSize::SIZE300
        );
});
