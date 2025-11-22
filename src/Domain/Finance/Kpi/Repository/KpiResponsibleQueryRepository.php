<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<KpiResponsible>
 */
class KpiResponsibleQueryRepository extends QueryRepository
{
    protected string $entityName = KpiResponsible::class;

    public function getActualResponsible(int $empId): ?KpiResponsible
    {
        $sql = <<<SQL
            select 
              r.id,
              r.change_date    change_date,
              r.change_user_id change_user_id,
              --------------------------------------
              r.user_id user_id_id,
              e.name    user_id_name,
              ds.name   user_id_response_name,
              --------------------------------------
              r.enterprise_id  enterprise_id_id,
              f.name           enterprise_id_name
            from tehno.kpi_responsible r
            join test.cp_emp e on e.id = r.user_id  
            join test.cp_emp_state es on es.employee = e.id and es.is_main = 1  
            join test.cp_depart_state ds on ds.id = es.state  
            join tehno.finclient f on f.id = r.enterprise_id
            join tehno.fin_salary fs on fs.enterprise = f.id and trunc(sysdate) between fs.from_date and fs.to_date
            join tehno.finemployee fe on fe.id = fs.employee
            where fe.cp_id = :empId
        SQL;

        return $this->query($sql, [
            'empId' => $empId,
        ])->first();
    }

    /**
     * @return Enumerable<int, KpiResponsible>
     */
    public function getResponsibles(array $ids = []): Enumerable
    {
        $sql = <<<SQL
            select 
              r.id,
              r.change_date    change_date,
              r.change_user_id change_user_id,
              --------------------------------------
              r.user_id user_id_id,
              e.name    user_id_name,
              ds.name   user_id_response_name,
              --------------------------------------
              r.enterprise_id  enterprise_id_id,
              f.name           enterprise_id_name
            from tehno.kpi_responsible r
            join test.cp_emp e on e.id = r.user_id
            join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
            join test.cp_depart_state ds on ds.id = es.state
            join tehno.finclient f on f.id = r.enterprise_id
        SQL;

        if ($ids !== []) {
            $sql .= ' where r.id in (' . implode(',', $ids) . ')';
        }

        return $this->query($sql);
    }

    public function getResponsible(int $id): KpiResponsible
    {
        $sql = <<<SQL
            select 
              r.id,
              r.change_date    change_date,
              r.change_user_id change_user_id,
              --------------------------------------
              r.user_id user_id_id,
              e.name    user_id_name,
              ds.name   user_id_response_name,
              --------------------------------------
              r.enterprise_id  enterprise_id_id,
              f.name           enterprise_id_name
            from tehno.kpi_responsible r
            join test.cp_emp e on e.id = r.user_id
            join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
            join test.cp_depart_state ds on ds.id = es.state
            join tehno.finclient f on f.id = r.enterprise_id
            where r.id = :id
        SQL;

        $responsible = $this->query($sql, [
            'id' => $id,
        ])->first();
        if ($responsible === null) {
            throw new EntityNotFoundDatabaseException("Не найден ответственный с id = $id");
        }
        return $responsible;
    }

    public function findResponsible(string $q): array
    {
        $sql = <<<SQL
            select 
                e.id id,
                e.name name,
                r.name response_name,
                d.name department_name
            from test.cp_emp e
            join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
            join test.cp_depart_state ds on ds.id = es.state
            join test.cp_departament d on d.id = ds.depart 
            join test.cp_response r on r.id = ds.response
            where e.active = 'Y'
            and e.is_technical = 0
            and lower(e.name) like '%' || lower(:q) || '%'
        SQL;

        $data = iterator_to_array($this->conn->query($sql, [
            'q' => mb_strtolower($q),
        ]));
        return array_map(static fn ($x): array => [
            'id'              => (int) $x['id'],
            'name'            => $x['name'],
            'response_name'   => $x['response_name'],
            'department_name' => $x['department_name'],
        ], $data);
    }

    public function findEnterprises(string $q): array
    {
        $sql = <<<SQL
            SELECT DISTINCT
                   fs.enterprise id,
                   ent.name name
            FROM tehno.fin_salary fs
            JOIN tehno.finclient  ent  ON  ent.id = fs.enterprise
            WHERE EXISTS (
            SELECT null 
              FROM tehno.kpi_accured_history  kah 
             WHERE kah.employee_id = fs.employee
              )
            and lower(ent.name) like '%' || lower(:q) || '%'
        SQL;

        $data = iterator_to_array($this->conn->query($sql, [
            'q' => mb_strtolower($q),
        ]));
        return array_map(static fn ($x): array => [
            'id'   => (int) $x['id'],
            'name' => $x['name'],
        ], $data);
    }
}
