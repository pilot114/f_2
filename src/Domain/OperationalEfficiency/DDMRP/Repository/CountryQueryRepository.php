<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Country;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/** @extends QueryRepository<Country> */
class CountryQueryRepository extends QueryRepository
{
    protected string $entityName = Country::class;

    /** @return Enumerable<int, Country> */
    public function getCountryList(): Enumerable
    {
        $sql = <<<SQL
         SELECT DISTINCT
          c.country id
        , c.name
        FROM tehno.country c
        JOIN tehno.sklads s 
             ON s.country_id = c.country
        JOIN test.cp_cok_info cci 
             ON cci.contract = s.type || s.contract
        ORDER BY c.name
        SQL;

        return $this->query($sql);
    }
}
