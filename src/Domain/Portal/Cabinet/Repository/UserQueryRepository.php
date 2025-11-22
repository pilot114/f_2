<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\User;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<User>
 */
class UserQueryRepository extends QueryRepository
{
    protected string $entityName = User::class;

    /**
     * @return Enumerable<int, User>
     */
    public function findActiveUsersWithEmail(string $search): Enumerable
    {
        $sql = "
                select
                e.id,
                e.name,
                e.email,
                -----------------------
                r.id responses_id,
                r.name responses_name
                
                from test.cp_emp e
                left join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
                left join test.cp_depart_state ds on ds.id = es.state
                left join test.cp_response r on r.id = ds.response
                where 1=1
                and e.is_technical = 0
                and e.active = 'Y'
                and lower(e.name) LIKE '%' || lower(:search) || '%'";

        return $this->query($sql, [
            'search' => $search,
        ]);
    }

    /**
     * @return Enumerable<int, User>
     */
    public function getUsersByIds(array $userIds): Enumerable
    {
        $sql = "
                select
                e.id,
                e.name,
                e.email,
                -----------------------
                r.id responses_id,
                r.name responses_name
                
                from test.cp_emp e
                left join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
                left join test.cp_depart_state ds on ds.id = es.state
                left join test.cp_response r on r.id = ds.response
                where 1=1
                and e.id in (:userIds)";

        return $this->query($sql,
            [
                'userIds' => $userIds,
            ],
            [
                'userIds' => ParamType::ARRAY_INTEGER,
            ]
        );
    }
}
