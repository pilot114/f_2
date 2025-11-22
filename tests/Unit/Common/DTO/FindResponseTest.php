<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\FindResponse;
use App\Common\Helper\EnumerableWithTotal;
use DateTimeImmutable;
use Illuminate\Support\Collection;

// Test object for recursive functionality
class TestObject
{
    public function __construct(
        public int $id,
        public string $name,
        public array $children = [],
        public ?TestObject $parent = null,
        public ?DateTimeImmutable $date = null
    ) {
    }
}

beforeEach(function (): void {
    // Регистрируем макрос getTotal для Collection в тестах
    if (!Collection::hasMacro('getTotal')) {
        Collection::macro('getTotal', function () {
            return $this->count();
        });
    }
});

it('creates find response with array items', function (): void {
    $items = ['item1', 'item2', 'item3'];
    $response = new FindResponse($items);

    expect($response->items)->toBe(['item1', 'item2', 'item3']);
    expect($response->total)->toBe(3);
});

it('creates find response with explicit total', function (): void {
    $items = ['item1', 'item2'];
    $response = new FindResponse($items, 10);

    expect($response->items)->toBe(['item1', 'item2']);
    expect($response->total)->toBe(10);
});

it('creates find response with empty array', function (): void {
    $response = new FindResponse([]);

    expect($response->items)->toBe([]);
    expect($response->total)->toBe(0);
});

it('creates find response with collection', function (): void {
    $collection = new Collection(['item1', 'item2', 'item3']);
    $response = new FindResponse($collection);

    expect($response->items)->toBe(['item1', 'item2', 'item3']);
    expect($response->total)->toBe(3);
});

it('creates find response with enumerable having custom total', function (): void {
    // Используем EnumerableWithTotal для создания коллекции с кастомным total
    $enumerable = EnumerableWithTotal::build(['item1', 'item2'], 10);
    $response = new FindResponse($enumerable);

    expect($response->items)->toBe(['item1', 'item2']);
    expect($response->total)->toBe(10);
});

it('reindexes associative arrays to sequential arrays', function (): void {
    $items = [
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ];
    $response = new FindResponse($items);

    expect($response->items)->toBe(['value1', 'value2', 'value3']);
    expect(array_keys($response->items))->toBe([0, 1, 2]);
});

it('handles recursive object arrays correctly', function (): void {
    $child1 = new TestObject(1, 'Child 1');
    $child2 = new TestObject(2, 'Child 2');

    $parent = new TestObject(
        id: 1,
        name: 'Parent',
        children: [$child1, $child2]
    );

    $response = new FindResponse([$parent]);
    $response->recursive();

    expect($response->items)->toHaveCount(1);
    expect($response->items[0]->children)->toHaveCount(2);
    expect(array_keys($response->items[0]->children))->toBe([0, 1]);
});

it('preserves datetime objects in recursive processing', function (): void {
    $date = new DateTimeImmutable('2024-01-15');
    $obj = new TestObject(1, 'Test', [], null, $date);

    $response = new FindResponse([$obj]);
    $response->recursive();

    expect($response->items[0]->date)->toBe($date);
    expect($response->items[0]->date)->toBeInstanceOf(DateTimeImmutable::class);
});

it('handles nested objects recursively', function (): void {
    $grandChild = new TestObject(3, 'GrandChild');
    $child = new TestObject(2, 'Child', [$grandChild]);
    $parent = new TestObject(1, 'Parent', [$child]);

    $response = new FindResponse([$parent]);
    $response->recursive();

    expect($response->items[0]->children[0]->children)->toHaveCount(1);
    expect(array_keys($response->items[0]->children[0]->children))->toBe([0]);
});

it('handles empty object arrays in recursive processing', function (): void {
    $obj = new TestObject(1, 'Test', []);

    $response = new FindResponse([$obj]);
    $response->recursive();

    expect($response->items[0]->children)->toBe([]);
});

it('handles mixed arrays in recursive processing', function (): void {
    $obj1 = new TestObject(1, 'Object 1');
    $obj2 = new TestObject(2, 'Object 2');
    $parent = new TestObject(1, 'Parent', [$obj1, 'string', $obj2]);

    $response = new FindResponse([$parent]);
    $response->recursive();

    // Mixed arrays should not be reindexed if not all elements are objects
    expect($response->items[0]->children)->toBe([$obj1, 'string', $obj2]);
});

it('returns self from recursive method', function (): void {
    $response = new FindResponse(['test']);

    $result = $response->recursive();

    expect($result)->toBe($response);
});

it('handles null values in object properties', function (): void {
    $obj = new TestObject(1, 'Test', [], null);

    $response = new FindResponse([$obj]);
    $response->recursive();

    expect($response->items[0]->parent)->toBeNull();
});

it('handles object with all object values in properties', function (): void {
    $child1 = new TestObject(1, 'Child 1');
    $child2 = new TestObject(2, 'Child 2');

    // Создаем объект где все значения свойств являются объектами (не DateTimeInterface)
    $container = new class($child1, $child2) {
        public function __construct(
            public object $obj1,
            public object $obj2
        ) {
        }
    };

    $response = new FindResponse([$container]);
    $response->recursive();

    expect($response->items[0]->obj1)->toBeObject();
    expect($response->items[0]->obj2)->toBeObject();
});

it('handles deeply nested object structures', function (): void {
    $level3 = new TestObject(3, 'Level 3');
    $level2 = new TestObject(2, 'Level 2', [], $level3);
    $level1 = new TestObject(1, 'Level 1', [], $level2);

    $response = new FindResponse([$level1]);
    $response->recursive();

    expect($response->items[0]->parent->parent)->toBeObject();
    expect($response->items[0]->parent->parent->name)->toBe('Level 3');
});

it('handles non-object items in recursive processing', function (): void {
    $items = [
        'string',
        123,
        true,
        null,
    ];

    $response = new FindResponse($items);
    $response->recursive();

    expect($response->items)->toBe(['string', 123, true, null]);
});
