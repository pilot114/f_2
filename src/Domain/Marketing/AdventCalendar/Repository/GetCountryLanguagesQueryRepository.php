<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Marketing\AdventCalendar\Entity\CountryLanguage;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<CountryLanguage>
 */
class GetCountryLanguagesQueryRepository extends QueryRepository
{
    protected string $entityName = CountryLanguage::class;

    /**
     * @procedure tehno.shop_cursor_util.Get_Languages_Of_Country
     * @comment Возвращает список языков, действующих для страны для мобильных приложений
     * @return Enumerable<int, CountryLanguage>
     *
     * ```sql
     * procedure Get_Languages_Of_Country(pCountry in varchar2, o_result out sys_refcursor
     * ) is
     * begin
     *   open o_result for
     *      select cl.lang, cl.main, l.name
     *      from test.nc_product_country_langs cl, test.ml_langs l
     *      where cl.country=pCountry and l.lang=cl.lang and cl.active=1 and cl.page=0;
     * end;
     * ```
     */
    public function getLanguagesOfCountry(string $countryId): Enumerable
    {
        /** @var array<int, array> $result */
        $result = $this->conn->procedure('tehno.shop_cursor_util.Get_Languages_Of_Country', [
            'pCountry' => $countryId,
            'o_result' => null,
        ], [
            'pCountry' => [ParamMode::IN, ParamType::STRING],
            'o_result' => [ParamMode::OUT, ParamType::CURSOR],
        ])['o_result'];

        return EnumerableWithTotal::build($result)->map(function (array $item): CountryLanguage {
            return new CountryLanguage($item['lang'], (bool) (int) $item['main'], $item['name']);
        });

    }
}
