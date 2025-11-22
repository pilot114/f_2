<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Offer>
 */
class GetOfferQueryRepository extends QueryRepository
{
    protected string $entityName = Offer::class;
    public const STATIC_URL = "https://static.siberianhealth.com";
    /**
     * Получение данных адвента по магазину.
     */
    public function getData(int $offerId): ?Offer
    {
        $sql = <<<SQL
               SELECT
                om.id
                , oml.type offer_langs_type_name
                , oml.short_title offer_langs_short_title
                , oml.short_descr offer_langs_short_descr
                , bi.id offer_background_image_id
                , oml.image_url offer_langs_image_url
                , oml.description offer_langs_full_descr
                , oml.button_text offer_langs_button_text 
                , oml.news_link offer_langs_news_link
                , oml.lang offer_langs_id
                , l2.name offer_langs_name
                , cf.id offer_langs_image_id
                , CASE
                    WHEN tehno.shop_cursor_util.Get_Main_Language(s.shop) = oml.lang
                      THEN 1
                        ELSE 0
                          END offer_langs_is_main
                FROM test.nc_offer_of_month om 
                LEFT JOIN test.nc_offer_of_month_lang oml ON oml.offer_id = om.id
                LEFT JOIN test.ml_langs l2 ON l2.lang = oml.lang
                JOIN test.nc_calendar_of_shops s ON s.id = om.calendar_id
                JOIN test.nc_month_dict md ON md.month = s.month                
                JOIN test.ml_dict dc ON dc.id = md.dict_id
                LEFT JOIN test.nc_background_image bi ON bi.id = om.bk_image_id
                LEFT JOIN test.CP_FILES cf ON oml.image_url = cf.fpath
                WHERE om.id = :offer_id
                ORDER BY offer_langs_is_main DESC 
            SQL;

        return $this->query(
            $sql,
            [
                'offer_id' => $offerId,
            ],
            [
                'pCountry' => ParamType::INTEGER,
            ]
        )->map(function (Offer $offer): Offer {
            $offer->langs = array_values($offer->langs);
            return $offer;
        })->first();
    }

    /**
     * Получение данных адвента по магазину.
     */
    public function getOfferImage(int $imageId): string
    {
        $sql = <<<SQL
            SELECT cf.fpath 
            FROM test.cp_files cf 
            WHERE cf.id = :image_id
        SQL;

        $fpath = $this->conn->query(
            $sql,
            [
                'image_id' => $imageId,
            ],
            [
                'image_id' => ParamType::INTEGER,
            ]
        )->current()['fpath'];

        return $fpath ? self::STATIC_URL . $fpath : '';
    }
}
