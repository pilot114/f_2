<?php

declare(strict_types=1);

namespace App\Common\Helper;

use DateTimeImmutable;

class PeriodFormatter
{
    /**
     * Преобразует дату в строку вида "Месяц Год", например "Март 2023"
     */
    public static function getMonthlyPeriodTitle(DateTimeImmutable $month): string
    {
        return DateHelper::ruDateFormat($month, 'LLLL yyyy');
    }

    /**
     * Преобразует дату в строку вида "Месяц-Месяц Год", например "Март-Апрель 2023"
     */
    public static function getBimonthlyPeriodTitle(DateTimeImmutable $month): string
    {
        $monthIndex = (int) $month->format('n');
        $year = DateHelper::ruDateFormat($month, 'yyyy');
        if ($monthIndex % 2 === 0) {
            $start = DateHelper::ruDateFormat($month->modify('-1 month'), 'LLLL');
            $end = DateHelper::ruDateFormat($month, 'LLLL');
        } else {
            $start = DateHelper::ruDateFormat($month, 'LLLL');
            $end = DateHelper::ruDateFormat($month->modify('+1 month'), 'LLLL');
        }
        return "$start-$end $year";
    }

    /**
     * Преобразует дату в строку вида "Квартал Год", например "I квартал 2023"
     */
    public static function getQuarterlyPeriodTitle(DateTimeImmutable $month): string
    {
        $monthIndex = (int) $month->format('n');
        $year = DateHelper::ruDateFormat($month, 'yyyy');
        /** @var int<1, 4> $quarterIndex */
        $quarterIndex = (int) ceil($monthIndex / 3);
        $quarter = match ($quarterIndex) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
        };
        return "$quarter квартал $year";
    }
}
