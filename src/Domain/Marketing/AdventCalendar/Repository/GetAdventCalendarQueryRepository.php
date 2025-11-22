<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<AdventItem>
 */
class GetAdventCalendarQueryRepository extends QueryRepository
{
    protected string $entityName = AdventItem::class;

    /**
     * Получение данных адвента по магазину.
     * @return Enumerable<int, AdventItem>
     */
    public function getData(?string $shopId): Enumerable
    {
        $sql = <<<SQL
            SELECT 
              EXTRACT(YEAR FROM dates.month_date)||EXTRACT(MONTH FROM dates.month_date) id
            , s.id calendar_id
            , EXTRACT(MONTH FROM dates.month_date) params_month
            , dc.name params_name
            , EXTRACT(YEAR FROM dates.month_date) params_year 
            , sl.title params_lang_title
            , sl.label params_lang_label
            , sl.lang params_lang_id
            , l1.name params_lang_name
            , CASE
                WHEN tehno.shop_cursor_util.Get_Main_Language(s.shop) = sl.lang
                  THEN 1
                    ELSE 0
                      END params_lang_main
            , pm.id products_id
            , pm.sku products_code
            , t.name products_name
            , om.id offers_id
            , oml.short_descr offers_langs_short_descr
            , oml.short_title offers_langs_short_title
            , oml.image_url offers_langs_image_url
            , oml.type offers_langs_type_name
            , oml.lang offers_langs_id
            , l2.name offers_langs_name
            , CASE
                WHEN tehno.shop_cursor_util.Get_Main_Language(s.shop) = oml.lang
                  THEN 1
                    ELSE 0
                      END offers_langs_main
            , CASE 
                WHEN oml.type IS NOT NULL AND oml.short_title IS NOT NULL AND oml.short_descr IS NOT NULL AND oml.description IS NOT NULL
                     AND oml.image_url IS NOT NULL AND oml.news_link IS NOT NULL AND om.bk_image_id IS NOT NULL
                  THEN 1
                    ELSE 0
                      END offers_langs_full_description
            FROM (SELECT
                    ADD_MONTHS(date '2025-10-01', LEVEL - 1) AS month_date
                  FROM dual
                  CONNECT BY
                    LEVEL <= MONTHS_BETWEEN(date '2026-09-01', date '2025-09-01') ) dates
            JOIN test.nc_month_dict md on md.month = EXTRACT(MONTH FROM dates.month_date) 
            JOIN test.ml_dict dc on dc.id = md.dict_id
            LEFT JOIN test.nc_calendar_of_shops s ON s.month =  md.month AND s.year = EXTRACT(YEAR FROM dates.month_date) AND s.shop = :shop_id
            LEFT JOIN test.nc_calendar_of_shops_lang sl ON sl.calendar_id = s.id 
            LEFT JOIN test.ml_langs l1 ON l1.lang = sl.lang
            LEFT JOIN test.nc_product_of_month pm ON pm.calendar_id = s.id
            LEFT JOIN tehno.tovar t ON t.code = pm.sku
            LEFT JOIN test.nc_offer_of_month om ON om.calendar_id = s.id AND om.active = 1
            LEFT JOIN test.nc_offer_of_month_lang oml ON oml.offer_id = om.id
            LEFT JOIN test.ml_langs l2 ON l2.lang = oml.lang
            ORDER BY params_year ASC, params_month ASC , s.id ASC, pm.id ASC, om.id  ASC
            SQL;
        return $this->query(
            $sql,
            [
                'shop_id' => $shopId,
            ],
            [
                'shop_id' => ParamType::STRING,
            ]
        );
    }
}
