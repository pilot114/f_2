<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\DeputyUser;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<DeputyUser>
 */
class DeputyUserQueryRepository extends QueryRepository
{
    protected string $entityName = DeputyUser::class;

    public function findByUserId(int $userId): ?DeputyUser
    {
        $sql = <<<SQL
            select
                e.id id,
                e.name name,
                ds.id positions_id,
                ds.name positions_name,
                d.id departments_id,
                d.name departments_name
            from test.cp_emp e
            join test.cp_depart_state ds on ds.current_employee = e.id
            join test.cp_departament d on d.id = ds.depart
            where e.id = :userId
        SQL;

        return $this->query($sql, [
            'userId' => $userId,
        ])->first();
    }
}
