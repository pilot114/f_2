<?php

declare(strict_types=1);

namespace App\Common\Helper;

class CountableFormatter
{
    /**
     * Возвращает число объектов в грамматически правильном виде
     *
     * $y = CountableFormatter::pluralize (5, ['год', 'года', 'лет']); // 5 лет
     *
     * @param int $number - число объектов
     * @param array $titles - варианты написания
     * @param bool $emptyWhenZero - если true (по умолчанию) то при 0 объектов возвращает пустую строку
     */
    public static function pluralize(int $number, array $titles, bool $emptyWhenZero = true): string
    {
        if ($number === 0 && $emptyWhenZero) {
            return "";
        }
        $cases = [2, 0, 1, 1, 1, 2];
        $index = ($number % 100 > 4 && $number % 100 < 20)
            ? 2
            : $cases[
            ($number % 10 < 5)
                ? $number % 10
                : 5
            ];
        return "$number " . $titles[$index];
    }
}
