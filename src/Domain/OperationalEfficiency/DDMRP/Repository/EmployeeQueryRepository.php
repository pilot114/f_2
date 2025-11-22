<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use App\Domain\OperationalEfficiency\DDMRP\Entity\RegionalDirector;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/** @extends QueryRepository<CokEmployee> */
class EmployeeQueryRepository extends QueryRepository
{
    protected string $entityName = CokEmployee::class;

    /** @return Enumerable<int, RegionalDirector> */
    public function getRegionalDirectors(): Enumerable
    {
        $sql = <<<SQL
        SELECT DISTINCT 
          reg.id
        , reg.name
        FROM ( SELECT 
                e1.id
              , e1.name
              , e1.flag_region_director
              , e1.active
              FROM test.cp_emp e1
              WHERE e1.active = 'Y'
              AND e1.flag_region_director = 'Y'
              UNION ALL
              SELECT 
                ci1.rd_id
              , e2.name
              , e2.flag_region_director
              , e2.active
              FROM  test.cp_cok_info ci1
              JOIN test.cp_emp e2 
                   ON e2.id = ci1.rd_id 
              WHERE e2.active = 'N'
              OR e2.flag_region_director = 'N' ) reg
        ORDER BY reg.name
        SQL;

        $raw = $this->conn->query($sql);

        return $this->customDenormalizeToCollection($raw, RegionalDirector::class);
    }
}
