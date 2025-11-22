<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Marketing\AdventCalendar\Entity\Shop;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Shop>
 */
class GetShopsQueryRepository extends QueryRepository
{
    protected string $entityName = Shop::class;

    /**
     * @procedure tehno.shop_cursor_utils.Get_List_Of_Shops
     * @comment Возвращает коды ИМ, которые требуют перезагрузки цен
     * @return Enumerable<int, Shop>
     *
     * ```sql
     * procedure Get_List_Of_Shops(p_Lang in varchar2 default null,
     *                             o_result out sys_refcursor)
     * is
     * begin
     *   if p_Lang is null then
     *     open o_result for
     *       with Qry (shop)
     *       as
     *       (
     *          select distinct cntr
     *          from v_dispatch_routing
     *       )
     *       select Qry.shop, cn.name, cn.name_ru, nvl(csp.display_100_units, 0) as display_100_units
     *       from Qry
     *       inner join test.ml_cntrs cn on cn.cntr = Qry.shop
     *       left outer join tehno.country_shop_properties csp on csp.country = Qry.shop
     *       order by shop;
     *   elsif p_Lang = 'ru' then
     *     open o_result for
     *       with Qry (shop)
     *       as
     *       (
     *          select distinct cntr
     *          from v_dispatch_routing
     *       )
     *       select Qry.shop, cn.name_ru as name, cn.name_ru, nvl(csp.display_100_units, 0) as display_100_units
     *       from Qry
     *       inner join test.ml_cntrs cn on cn.cntr = Qry.shop
     *       left outer join tehno.country_shop_properties csp on csp.country = Qry.shop
     *       order by shop;
     *   else
     *     open o_result for
     *       with Qry (shop)
     *       as
     *       (
     *          select distinct cntr
     *          from v_dispatch_routing
     *       ),
     *       Trans(name, value)
     *       as
     *       (
     *          select dc.name, dl.value
     *          from test.ML_DICT dc
     *          left outer join test.ML_DICT_LANG dl on dl.id = dc.id and dl.LANG = p_Lang and dl.CNTR is null
     *       )
     *       select Qry.shop, nvl(Trans.value, cn.name) as name, cn.name_ru, nvl(csp.display_100_units, 0) as display_100_units
     *       from Qry
     *       inner join test.ml_cntrs cn on cn.cntr = Qry.shop
     *       left outer join Trans on Trans.name = cn.name_ru
     *       left outer join tehno.country_shop_properties csp on csp.country = Qry.shop
     *       order by shop;
     *   end if;
     * end;
     * ```
     */
    public function getListOfShops(?string $lang = null): Enumerable
    {
        /** @var array<int, array> $result */
        $result = $this->conn->procedure('tehno.shop_cursor_utils.Get_List_Of_Shops', [
            'p_Lang'   => $lang,
            'o_result' => null,
        ], [
            'p_Lang'   => [ParamMode::IN, ParamType::STRING],
            'o_result' => [ParamMode::OUT, ParamType::CURSOR],
        ])['o_result'];

        return EnumerableWithTotal::build($result)->map(function (array $item): Shop {
            return new Shop($item['shop'], $item['name'], $item['name_ru']);
        });
    }
}
