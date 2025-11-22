<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Helper;

use App\Common\Helper\CountableFormatter;

it('returns empty string for zero with emptyWhenZero true', function (): void {
    $result = CountableFormatter::pluralize(0, ['яблоко', 'яблока', 'яблок']);

    expect($result)->toBe('');
});

it('returns formatted string for zero with emptyWhenZero false', function (): void {
    $result = CountableFormatter::pluralize(0, ['яблоко', 'яблока', 'яблок'], false);

    expect($result)->toBe('0 яблок');
});

it('pluralizes one item correctly', function (): void {
    $result = CountableFormatter::pluralize(1, ['яблоко', 'яблока', 'яблок'], false);

    expect($result)->toBe('1 яблоко');
});

it('pluralizes two items correctly', function (): void {
    $result = CountableFormatter::pluralize(2, ['яблоко', 'яблока', 'яблок'], false);

    expect($result)->toBe('2 яблока');
});

it('pluralizes five items correctly', function (): void {
    $result = CountableFormatter::pluralize(5, ['яблоко', 'яблока', 'яблок'], false);

    expect($result)->toBe('5 яблок');
});

it('pluralizes numbers ending in 1 correctly', function (int $number): void {
    $result = CountableFormatter::pluralize($number, ['год', 'года', 'лет'], false);

    expect($result)->toBe("$number год");
})->with([1, 21, 31, 41, 101, 121]);

it('pluralizes numbers ending in 2-4 correctly', function (int $number): void {
    $result = CountableFormatter::pluralize($number, ['год', 'года', 'лет'], false);

    expect($result)->toBe("$number года");
})->with([2, 3, 4, 22, 23, 24, 32, 33, 34]);

it('pluralizes numbers ending in 5-9 and 0 correctly', function (int $number): void {
    $result = CountableFormatter::pluralize($number, ['год', 'года', 'лет'], false);

    expect($result)->toBe("$number лет");
})->with([5, 6, 7, 8, 9, 10, 15, 20, 25, 30]);

it('handles special cases 11-14 correctly', function (int $number): void {
    $result = CountableFormatter::pluralize($number, ['год', 'года', 'лет'], false);

    expect($result)->toBe("$number лет");
})->with([11, 12, 13, 14, 111, 112, 113, 114]);

it('pluralizes large numbers correctly', function (): void {
    expect(CountableFormatter::pluralize(101, ['день', 'дня', 'дней'], false))->toBe('101 день')
        ->and(CountableFormatter::pluralize(102, ['день', 'дня', 'дней'], false))->toBe('102 дня')
        ->and(CountableFormatter::pluralize(105, ['день', 'дня', 'дней'], false))->toBe('105 дней')
        ->and(CountableFormatter::pluralize(111, ['день', 'дня', 'дней'], false))->toBe('111 дней')
        ->and(CountableFormatter::pluralize(1001, ['день', 'дня', 'дней'], false))->toBe('1001 день');
});

it('handles different word forms', function (): void {
    expect(CountableFormatter::pluralize(1, ['файл', 'файла', 'файлов'], false))->toBe('1 файл')
        ->and(CountableFormatter::pluralize(2, ['файл', 'файла', 'файлов'], false))->toBe('2 файла')
        ->and(CountableFormatter::pluralize(5, ['файл', 'файла', 'файлов'], false))->toBe('5 файлов');
});

it('default emptyWhenZero is true', function (): void {
    $result = CountableFormatter::pluralize(0, ['элемент', 'элемента', 'элементов']);

    expect($result)->toBe('');
});
