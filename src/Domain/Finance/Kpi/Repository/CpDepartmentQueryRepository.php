<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<CpDepartment>
 */
class CpDepartmentQueryRepository extends QueryRepository
{
    protected string $entityName = CpDepartment::class;

    /**
     * @return Enumerable<int, CpDepartment>
     */
    public function getDepartments(): Enumerable
    {
        $sql = <<<SQL
            select
                d.id,
                d.name
            from test.cp_departament d
            where d.close_date is null
            and (d.for_kpi = 1 or d.for_kpi_s = 1)
            order by d.name
        SQL;

        return $this->query($sql);
    }
}
