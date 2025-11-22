<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Marketing\AdventCalendar\Entity\BackgroundImage;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<BackgroundImage>
 */
class GetBackgroundImagesQueryRepository extends QueryRepository
{
    protected string $entityName = BackgroundImage::class;

    /**
     * Получение данных адвента по магазину.
     * @return Enumerable<int, BackgroundImage>
     * @procedure tehno.shop_cursor_calendar.Get_List_Of_Background_Images
     * @comment Возвращает список фоновых картинок для админки
     *
     * ```sql
     * procedure Get_List_Of_Background_Images(o_result  out sys_refcursor)
     * is
     * begin
     *   open o_result for
     *     select *
     *     from test.nc_background_image
     *     order by id;
     * end;
     * ```
     */
    public function getListOfBackgroundImages(): Enumerable
    {
        /** @var array<int, array> $result */
        $result = $this->conn->procedure('tehno.shop_cursor_calendar.Get_List_Of_Background_Images', [
            'o_result' => null,
        ], [
            'o_result' => [ParamMode::OUT, ParamType::CURSOR],
        ])['o_result'];

        return EnumerableWithTotal::build($result)->map(function (array $item): BackgroundImage {
            $item['id'] = (int) $item['id'];
            return new BackgroundImage(...$item);
        });
    }
}
