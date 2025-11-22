<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Repository;

use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<MonthProduct>
 */
class GetAdventProductQueryRepository extends QueryRepository
{
    protected string $entityName = MonthProduct::class;

    /**
     * Получение данных адвента по магазину.
     * @return Enumerable<int, MonthProduct>
     */
    public function getData(string $countryId ,?string $q): Enumerable
    {
        $qWhere = "";

        if ($q !== null) {
            $qWhere = "AND ( LOWER(t.code) LIKE '%' || LOWER (:q) || '%' OR LOWER(t.name) LIKE '%' || LOWER (:q) || '%' )";
        }

        $sql = <<<SQL
                SELECT t.id, t.code, t.name
                FROM tehno.tovar t
                WHERE tehno.shop_cursor_product.Check_Product(t.code, :pCountry) = 1
                $qWhere
            SQL;

        return $this->query(
            $sql,
            [
                'pCountry' => $countryId,
                'q'        => $q,
            ],
            [
                'pCountry' => ParamType::STRING,
                'q'        => ParamType::STRING,
            ]
        );
    }
}
