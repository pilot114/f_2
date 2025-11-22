<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Repository;

use App\Domain\Marketing\CustomerHistory\Entity\ProductCountry;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<ProductCountry>
 */
class ProductCountryQueryRepository extends QueryRepository
{
    protected string $entityName = ProductCountry::class;

    /**
     * @return Enumerable<int, ProductCountry>
     */
    public function getProductCountries(string $lang): Enumerable
    {
        $langSql = '';

        if (strlen($lang) === 2) {
            $langSql = 'AND l.lang = :lang';
        }

        return $this->query("
            SELECT c.cntr as id, c.name_ru 
            FROM test.nc_product_country_langs l 
            JOIN test.ml_cntrs c ON c.cntr = l.country 
            WHERE l.active = 1 
            AND l.page = 0
            $langSql
        ", [
            'lang' => $lang,
        ], [
            'lang' => ParamType::STRING,
        ]);
    }
}
