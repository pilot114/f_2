<?php

declare(strict_types=1);

namespace App\Common\Helper;

use DateTimeImmutable;
use DateTimeInterface;
use IntlDateFormatter;

class DateHelper
{
    protected DateTimeImmutable $date;
    public const MONTH_NAMES = [
        'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь',
    ];

    public function __construct(
        protected string $dateString
    ) {
        $this->date = new DateTimeImmutable($dateString);
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getRussianMonthAndYear(): string
    {
        $monthIndex = intval($this->date->format('m'));
        return self::MONTH_NAMES[$monthIndex - 1] . ' ' . $this->date->format('Y');
    }

    public static function ruDateFormat(
        DateTimeInterface $date,
        // пример: 5 июня 2024
        string $pattern = 'd MMMM yyyy'
    ): string {
        $formatter = new IntlDateFormatter(
            'ru_RU',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Asia/Novosibirsk',
            IntlDateFormatter::GREGORIAN,
            $pattern
        );
        return (string) $formatter->format($date);
    }
}
