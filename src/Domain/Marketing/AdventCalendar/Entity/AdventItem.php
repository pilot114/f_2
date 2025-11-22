<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

/**+
 * @TODO Публичные свойства, потому что FindResponse требует перезаписи полей
 */
#[Entity(name: 'test.nc_calendar_of_shops')]
class AdventItem
{
    public function __construct(
        // Ключ календарной записи (s.id)
        #[Column(name: 'id')] public int $id,
        // Общие параметры месяца
        #[Column(name: 'params')] public MonthParams $params,
        #[Column(name: 'calendar_id')] public ?int $calendarId = null,
        // Товары месяца
        #[Column(name: 'products', collectionOf: MonthProduct::class)] public array $products = [],
        // Предложения месяца с множеством языков
        #[Column(name: 'offers', collectionOf: MonthOffer::class)] public array $offers = [],
    ) {
    }
}
