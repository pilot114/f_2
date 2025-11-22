<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Repository;

use App\Domain\Hr\Achievements\Entity\Employee;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Employee>
 */
class EmployeeQueryRepository extends QueryRepository
{
    protected string $entityName = Employee::class;

    protected const BASE_SQL = "
        SELECT
        ce.id id,
        ce.name name,
        ds.name response
        FROM test.cp_emp ce
        LEFT JOIN test.cp_emp_state es ON es.employee = ce.id AND es.is_main = 1
             -- хак для получения только первой должности, если у пользователя их несколько
            AND es.id = (SELECT MIN(es2.id) FROM test.cp_emp_state es2 WHERE es2.employee = ce.id AND es2.is_main = 1)
        LEFT JOIN test.cp_depart_state ds ON ds.id = es.state
    ";

    public function getById(int $id): ?Employee
    {
        $sql = self::BASE_SQL . " WHERE ce.active = 'Y' AND ce.id = :id";

        return $this->query($sql, [
            'id' => $id,
        ])->first();
    }
}
