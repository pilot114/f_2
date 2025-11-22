<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<AdventItem>
 */
class WriteMonthCommandRepository extends CommandRepository
{
    protected string $entityName = AdventItem::class;

    /**
     * @procedure tehno.shop_cursor_calendar.Add_Period_To_Calendar
     * @comment Добавляет период в календарь
     *
     * ```sql
     * procedure Add_Period_To_Calendar(p_Year  in integer,
     *                                  p_Month in integer,
     *                                  p_Shop  in varchar2,
     *                                  p_Out   out integer)
     * is
     * begin
     *   insert into test.nc_calendar_of_shops (year, month, shop)
     *   values (p_Year, p_Month, p_Shop)
     *   returning id into p_Out;
     * end;
     * ```
     */
    public function addPeriodToCalendar(int $year, int $month, string $shop): int
    {
        return (int) $this->conn->procedure('tehno.shop_cursor_calendar.Add_Period_To_Calendar', [
            'p_Year'  => $year,
            'p_Month' => $month,
            'p_Shop'  => $shop,
            'p_Out'   => null,
        ], [
            'p_Year'  => [ParamMode::IN, ParamType::INTEGER],
            'p_Month' => [ParamMode::IN, ParamType::INTEGER],
            'p_Shop'  => [ParamMode::IN, ParamType::STRING],
            'p_Out'   => [ParamMode::OUT, ParamType::INTEGER],
        ])["p_Out"];
    }

    /**
     * @procedure tehno.shop_cursor_calendar.Add_Update_Period_Lang
     * @comment Добавляет или обновляет локализованный период
     *
     * ```sql
     * procedure Add_Update_Period_Lang(p_Calendar_Id in integer,
     *                                  p_Lang  in varchar2,
     *                                  p_Title in varchar2,
     *                                  p_Label in varchar2)
     * is
     * begin
     *   update test.nc_calendar_of_shops_lang
     *   set title = p_Title, label = p_Label
     *   where calendar_id = p_Calendar_Id and lang = p_Lang;
     *   if SQL%ROWCOUNT = 0 then
     *     insert into test.nc_calendar_of_shops_lang (calendar_id, lang, title, label)
     *     values (p_Calendar_Id, p_Lang, p_Title, p_Label);
     *   end if;
     * end;
     * ```
     */
    public function updatePeriodLang(int $calendarId, string $lang, ?string $title, ?string $label): void
    {
        $this->conn->procedure('tehno.shop_cursor_calendar.Add_Update_Period_Lang', [
            'p_Calendar_Id' => $calendarId,
            'p_Lang'        => $lang,
            'p_Title'       => $title,
            'p_Label'       => $label,
        ], [
            'p_Calendar_Id' => [ParamMode::IN, ParamType::INTEGER],
            'p_Lang'        => [ParamMode::IN, ParamType::STRING],
            'p_Title'       => [ParamMode::IN, ParamType::STRING],
            'p_Label'       => [ParamMode::IN, ParamType::STRING],
        ]);
    }
}
