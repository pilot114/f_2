<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\Repository;

use App\Domain\Hr\Reinstatement\Entity\Employee;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Employee>
 */
class ReinstatementQueryRepository extends QueryRepository
{
    protected string $entityName = Employee::class;

    /** @return Enumerable<int, Employee> */
    public function getByNamePart(string $query): Enumerable
    {
        return $this->query("
        WITH dates AS ( SELECT DISTINCT
                es1.employee
                , max(es1.from_date) from_date
                , max(es1.to_date) to_date
                FROM test.cp_emp_state es1
                WHERE 1=1
                GROUP BY es1.employee )
        SELECT 
          emp.*
        , d.name emp_department_name
        , d.is_closed emp_department_is_closed
        FROM ( SELECT
                 e.id
               , e.name emp_name
               , NVL( e.dismiss_dt, 
                      CASE 
                       WHEN es.to_date = '01.01.3000'
                         THEN NULL
                           ELSE es.to_date
                             END ) emp_dismiss_dt
              , e.login emp_login
              , e.email emp_email
              , ds.id emp_state_id
              , NVL(ds.name, e.response) emp_state_name
              , ds.depart
              FROM test.cp_emp e
              JOIN dates 
                   ON dates.employee = e.id
              JOIN ( SELECT DISTINCT  
                            es.employee
                           , es.from_date  
                           , es.to_date
                           , es.state
                           FROM test.cp_emp_state es ) es 
                                ON es.employee = e.id
                                AND es.from_date = dates.from_date
                                AND es.to_date = dates.to_date
                           LEFT JOIN test.cp_depart_state ds
                   ON ds.id = es.state      
              WHERE 1=1
              AND e.active = 'N'
              UNION ALL
              SELECT
                e.id
              , e.name emp_name
              , e.dismiss_dt emp_dismiss_dt
              , e.login emp_login
              , e.email emp_email
              , NULL emp_state_id
              , e.response emp_state_name
              , e.iddepartament depart
              FROM test.cp_emp e
              WHERE e.active = 'N'
              AND NOT EXISTS ( SELECT NULL 
                               FROM test.cp_emp_state es 
                               WHERE es.employee = e.id ) ) emp 
        LEFT JOIN test.cp_departament d ON d.id = emp.depart
        WHERE LOWER(emp.emp_name) LIKE LOWER ('%$query%')
        ORDER BY emp.emp_name, emp.emp_dismiss_dt");
    }

}
