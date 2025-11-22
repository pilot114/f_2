<?php

declare(strict_types=1);

namespace App\Common\Helper;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Traversable;

/**
 *  Enumerable + macros 'getTotal'
 */
class EnumerableWithTotal
{
    /**
     * @template TKey of int
     * @template TValue of mixed
     * @param iterable<TKey, TValue> $items
     * @return Enumerable<TKey, TValue>
     */
    public static function build(iterable $items = [], int $total = 0): Enumerable
    {
        $getTotal = function () use ($total): int {
            /** @var Enumerable<int, mixed> $this */
            // @phpstan-ignore-next-line
            return $total === 0 ? $this->count() : $total;
        };

        if ($items instanceof Traversable) {
            LazyCollection::macro('getTotal', $getTotal);
            // @phpstan-ignore-next-line
            return new LazyCollection(fn (): Generator => $items);
        }

        Collection::macro('getTotal', $getTotal);
        return new Collection($items);
    }
}
