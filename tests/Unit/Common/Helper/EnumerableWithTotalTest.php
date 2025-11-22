<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Helper;

use App\Common\Helper\EnumerableWithTotal;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

it('builds collection from array with total', function (): void {
    $items = [1, 2, 3];
    $result = EnumerableWithTotal::build($items, 10);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(3)
        ->and($result->getTotal())->toBe(10);
});

it('builds collection from array without total uses count', function (): void {
    $items = [1, 2, 3, 4, 5];
    $result = EnumerableWithTotal::build($items, 0);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->getTotal())->toBe(5);
});

it('builds lazy collection from generator', function (): void {
    $generator = function () {
        yield 1;
        yield 2;
        yield 3;
    };

    $result = EnumerableWithTotal::build($generator(), 20);

    expect($result)->toBeInstanceOf(LazyCollection::class)
        ->and($result->getTotal())->toBe(20);
});

it('handles empty array', function (): void {
    $result = EnumerableWithTotal::build([], 0);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(0)
        ->and($result->getTotal())->toBe(0);
});

it('handles empty array with non-zero total', function (): void {
    $result = EnumerableWithTotal::build([], 100);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(0)
        ->and($result->getTotal())->toBe(100);
});

it('builds collection with associative array', function (): void {
    $items = [
        'a' => 1,
        'b' => 2,
        'c' => 3,
    ];
    $result = EnumerableWithTotal::build($items, 15);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(3)
        ->and($result->getTotal())->toBe(15);
});

it('total reflects provided value not actual count', function (): void {
    $items = [1, 2, 3];
    $result = EnumerableWithTotal::build($items, 1000);

    expect($result->count())->toBe(3)
        ->and($result->getTotal())->toBe(1000);
});
