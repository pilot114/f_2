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
class WriteProductCommandRepository extends CommandRepository
{
    protected string $entityName = AdventItem::class;

    /**
     * @procedure tehno.shop_cursor_calendar.Add_Product_Of_Month
     * @comment Добавляет продукт месяца
     *
     * ```sql
     * procedure Add_Product_Of_Month(p_Calendar_Id in integer,
     *                                p_Sku in varchar2,
     *                                p_Out out integer)
     * is
     * begin
     *   insert into test.nc_product_of_month (calendar_id, sku)
     *   values (p_Calendar_Id, p_Sku)
     *   returning id into p_Out;
     * end;
     * ```
     */
    public function addProductOfMonth(int $calendarId, string $sku): array
    {
        return $this->conn->procedure('tehno.shop_cursor_calendar.Add_Product_Of_Month', [
            'p_Calendar_Id' => $calendarId,
            'p_Sku'         => $sku,
            'p_Out'         => null,
        ], [
            'p_Calendar_Id' => [ParamMode::IN, ParamType::INTEGER],
            'p_Sku'         => [ParamMode::IN, ParamType::STRING],
            'p_Out'         => [ParamMode::OUT, ParamType::STRING],
        ]);
    }

    /**
     * @procedure tehno.shop_cursor_calendar.Delete_Product_Of_Month
     * @comment Удаляет продукт месяца
     *
     * ```sql
     * procedure Delete_Product_Of_Month(p_Id in integer)
     * is
     * begin
     *   delete from test.nc_product_of_month
     *   where id = p_Id;
     * end;
     * ```
     */
    public function deleteProductOfMonth(int $id): void
    {
        $this->conn->procedure('tehno.shop_cursor_calendar.Delete_Product_Of_Month', [
            'p_Id' => $id,
        ], [
            'p_Id' => [ParamMode::IN, ParamType::INTEGER],
        ]);
    }
}
