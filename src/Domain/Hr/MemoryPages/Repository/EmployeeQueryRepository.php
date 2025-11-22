<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\DTO\GetEmployeeListRequest;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Employee>
 */
class EmployeeQueryRepository extends QueryRepository
{
    protected string $entityName = Employee::class;

    /** @return Enumerable<int, Employee> */
    public function getList(GetEmployeeListRequest $request): Enumerable
    {
        $sql = "
                select
                e.id,
                e.name,
                r.id response_id,
                r.name response_name
                
                from test.cp_emp e
                left join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
                left join test.cp_depart_state ds on ds.id = es.state
                left join test.cp_response r on r.id = ds.response
                where 1=1
                and e.is_technical = 0
                and lower(e.name) LIKE '%' || lower(:search) || '%'";

        return $this->query($sql, [
            'search' => $request->search,
        ]);
    }
}
