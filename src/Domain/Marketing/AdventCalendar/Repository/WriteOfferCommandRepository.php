<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Offer>
 */
class WriteOfferCommandRepository extends CommandRepository
{
    protected string $entityName = Offer::class;

    /**
     * @procedure tehno.shop_cursor_calendar.Add_Offer
     * @comment Добавляет предложение
     *
     * ```sql
     * procedure Add_Offer(p_Calendar_Id in integer,
     *                     p_Type in varchar2,
     *                     p_Active in integer,
     *                     p_Bk_Image_Id in integer,
     *                     p_Out out integer)
     * is
     * begin
     *   insert into test.nc_offer_of_month (calendar_id, type, active, bk_image_id)
     *   values (p_Calendar_Id, p_Type, to_char(p_Active), p_Bk_Image_Id)
     *   returning id into p_Out;
     * end;
     * ```
     */
    public function addOffer(int $calendarId, ?string $type, int $active, int $bkImageId): array
    {
        return $this->conn->procedure('tehno.shop_cursor_calendar.Add_Offer', [
            'p_Calendar_Id' => $calendarId,
            'p_Type'        => $type,
            'p_Active'      => $active,
            'p_Bk_Image_Id' => $bkImageId,
            'p_Out'         => null,
        ], [
            'p_Calendar_Id' => [ParamMode::IN, ParamType::INTEGER],
            'p_Type'        => [ParamMode::IN, ParamType::STRING],
            'p_Active'      => [ParamMode::IN, ParamType::INTEGER],
            'p_Bk_Image_Id' => [ParamMode::IN, ParamType::INTEGER],
            'p_Out'         => [ParamMode::OUT, ParamType::INTEGER],
        ]);
    }

    /**
     * @procedure tehno.shop_cursor_calendar.Update_Offer
     * @comment Обновляет предложение
     *
     * ```sql
     * procedure Update_Offer(p_Id in integer,
     *                        p_Calendar_Id in integer,
     *                        p_Type in varchar2,
     *                        p_Active in integer,
     *                        p_Bk_Image_Id in integer)
     * is
     * begin
     *   update test.nc_offer_of_month
     *   set calendar_id = p_Calendar_Id, type = p_Type, active = to_char(p_Active), bk_image_id = p_Bk_Image_Id
     *   where id = p_Id;
     * end;
     * ```
     */
    public function updateOffer(int $id, int $calendarId, string $type, int $active, int $bkImageId): void
    {
        $this->conn->procedure('tehno.shop_cursor_calendar.Update_Offer', [
            'p_Id'          => $id,
            'p_Calendar_Id' => $calendarId,
            'p_Type'        => $type,
            'p_Active'      => $active,
            'p_Bk_Image_Id' => $bkImageId,
        ], [
            'p_Id'          => [ParamMode::IN, ParamType::INTEGER],
            'p_Calendar_Id' => [ParamMode::IN, ParamType::INTEGER],
            'p_Type'        => [ParamMode::IN, ParamType::STRING],
            'p_Active'      => [ParamMode::IN, ParamType::INTEGER],
            'p_Bk_Image_Id' => [ParamMode::IN, ParamType::INTEGER],
        ]);
    }

    /**
     * @procedure tehno.shop_cursor_calendar.Add_Update_Offer_Lang
     * @comment Добавляет запись или обновляет запись в локализованных предложениях
     *
     * ```sql
     * procedure Add_Update_Offer_Lang(p_Offer_Id in integer,
     *                                 p_Lang in varchar2,
     *                                 p_Type in varchar2,
     *                                 p_Button_Text in varchar2,
     *                                 p_Short_Title in varchar2,
     *                                 p_Short_Descr in varchar2,
     *                                 p_Title in varchar2,
     *                                 p_Description in varchar2,
     *                                 p_Image_Url in varchar2,
     *                                 p_News_Link in varchar2)
     * is
     * begin
     *   update test.nc_offer_of_month_lang
     *   set type = p_Type,
     *       button_text = p_Button_Text,
     *       short_title = p_Short_Title,
     *       short_descr =p_Short_Descr,
     *       title = p_Title,
     *       description = p_Description,
     *       image_url = p_Image_url,
     *       news_link = p_News_link
     *   where offer_id = p_Offer_Id and lang = p_Lang;
     *   if SQL%ROWCOUNT = 0 then
     *     insert into test.nc_offer_of_month_lang (offer_id, lang, type,
     *                 button_text, short_title, short_descr, title, description, image_url, news_link)
     *     values (p_Offer_Id, p_Lang, p_Type,
     *             p_Button_Text, p_Short_Title, p_Short_Descr, p_Title, p_Description, p_Image_url, p_News_link);
     *   end if;
     * end;
     * ```
     */
    public function updateOfferLang(
        int $offerId,
        string $lang,
        ?string $type,
        ?string $buttonText,
        string $shortTitle,
        ?string $shortDescr,
        string $title,
        ?string $description,
        ?string $imageUrl,
        ?string $newsLink,
    ): void {
        $this->conn->procedure('tehno.shop_cursor_calendar.Add_Update_Offer_Lang', [
            'p_Offer_Id'    => $offerId,
            'p_Lang'        => $lang,
            'p_Type'        => $type,
            'p_Button_Text' => $buttonText,
            'p_Short_Title' => $shortTitle,
            'p_Short_Descr' => $shortDescr,
            'p_Title'       => $title,
            'p_Description' => $description,
            'p_Image_Url'   => $imageUrl,
            'p_News_Link'   => $newsLink,
        ], [
            'p_Offer_Id'    => [ParamMode::IN, ParamType::INTEGER],
            'p_Lang'        => [ParamMode::IN, ParamType::STRING],
            'p_Type'        => [ParamMode::IN, ParamType::STRING],
            'p_Button_Text' => [ParamMode::IN, ParamType::STRING],
            'p_Short_Title' => [ParamMode::IN, ParamType::STRING],
            'p_Short_Descr' => [ParamMode::IN, ParamType::STRING],
            'p_Title'       => [ParamMode::IN, ParamType::STRING],
            'p_Description' => [ParamMode::IN, ParamType::STRING],
            'p_Image_Url'   => [ParamMode::IN, ParamType::STRING],
            'p_News_Link'   => [ParamMode::IN, ParamType::STRING],
        ]);
    }
}
