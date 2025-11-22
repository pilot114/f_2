<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Common\Service\File\FileService;
use App\Domain\Finance\Kpi\DTO\DepartmentResponse;
use App\Domain\Finance\Kpi\DTO\EmployeeSearchKpiResponse;
use App\Domain\Finance\Kpi\DTO\EmployeeSearchResponse;
use App\Domain\Finance\Kpi\DTO\PositionResponse;
use App\Domain\Finance\Kpi\Entity\Kpi;
use Database\ORM\QueryRepository;
use DateTimeImmutable;

/**
 * @extends QueryRepository<Kpi>
 */
class KpiEmployeeSearchQueryRepository extends QueryRepository
{
    protected string $entityName = Kpi::class;

    /**
     * @return EmployeeSearchResponse[]
     */
    public function searchEmployeeForDepartmentBoss(int $userId, string $search): array
    {
        $sql = $this->buildSearchQuery();

        $raw = $this->conn->query($sql, [
            'pUserId' => $userId,
            'pSearch' => $search,
        ]);

        return $this->mapToEmployeeSearchResponse($raw);
    }

    /**
     * @return EmployeeSearchResponse[]
     */
    public function searchEmployeeForAdmin(string $search): array
    {
        $sql = $this->buildSearchQuery(forAdmin: true);

        $raw = $this->conn->query($sql, [
            'pSearch' => $search,
        ]);

        return $this->mapToEmployeeSearchResponse($raw);
    }

    /**
     * Построение SQL запроса для поиска сотрудников
     */
    private function buildSearchQuery(bool $forAdmin = false): string
    {
        $departCondition = $forAdmin
            ? '2'
            : '(SELECT 
               ds1.depart 
               FROM test.cp_depart_state ds1 
               WHERE ds1.id = d.boss_state AND ds1.current_employee = :pUserId)';

        return <<<SQL
        SELECT DISTINCT
          e.id
        , e.name emp_name
        , e.active emp_is_active
        , e.is_technical emp_is_technical
        , CASE  
            WHEN fe.id IS NULL 
              THEN 0
              ELSE 1
          END emp_has_uu_id
        , CASE 
            WHEN (fh.has_kpi + fh.has_two_month_bonus + fh.has_four_month_bonus) < 1 
              THEN 1
              ELSE 0
          END emp_has_no_kpi
        , CASE
            WHEN fs.id IS NULL
              THEN 0
              ELSE 1
          END emp_has_salary_uu
        , fh.has_kpi emp_has_kpi
        , fh.has_two_month_bonus emp_has_two_month_kpi
        , fh.has_four_month_bonus emp_has_four_month_kpi
        , fs.from_date emp_kpi_last_change_date
        , states.id states_id
        , states.state_name
        , states.state_is_main
        , states.state_from_date 
        , states.state_dep_name
        , states.state_dep_for_kpi
        , boss_emp.name emp_dep_boss_name
        , boss_state.depart_boss_state_name boss_state_name
        , boss_state.lvl boss_state_lvl
        , f1.id emp_userpic_id
        , f2.id boss_userpic_id
        FROM test.cp_emp e
        JOIN test.cp_depart_state ds 
                  ON ds.current_employee = e.id
                  AND ds.depart IN (SELECT
                                    d.id
                                    FROM test.cp_departament d
                                    WHERE d.is_closed = 0 
                                    CONNECT BY d.idparent = PRIOR d.id 
                                    START WITH d.id = $departCondition
                                )
        LEFT JOIN tehno.finemployee fe ON fe.cp_id = e.id
        LEFT JOIN tehno.finemployee_kpi_has fh ON fh.employee_id = fe.id
             JOIN tehno.fin_salary fs ON fs.id = fh.fin_salary_id AND fs.to_date >= to_date('31.01.3000', 'DD.MM.YYYY')   
        LEFT JOIN (SELECT  
                      es.id
                    , es.employee emp_id
                    , ds2.id     state_id
                    , ds2.name   state_name
                    , es.is_main state_is_main
                    , es.from_date state_from_date
                    , d2.name state_dep_name
                    , d2.id state_dep_id
                    , d2.boss_state state_dep_boss_state_id
                    , d2.for_kpi state_dep_for_kpi
                    FROM test.cp_emp_state es 
                    JOIN test.cp_depart_state ds2 ON ds2.id = es.state
                    JOIN test.cp_departament d2 ON d2.id = ds2.depart
                    WHERE es.to_date = to_date('01.01.3000', 'DD.MM.YYYY')
        ) states ON states.emp_id = e.id
        LEFT JOIN (
              SELECT
                dep.id depart_id
              , dep_ds.id depart_boss_state_id
              , dep_ds.name depart_boss_state_name
              , dep_ds.current_employee
              , 1 lvl
              FROM test.cp_departament dep
              JOIN test.cp_depart_state dep_ds ON dep_ds.id = dep.boss_state
              UNION ALL
              SELECT
                dep.id depart_id
              , dep_parent_ds.id depart_boss_state_id
              , dep_parent_ds.name depart_boss_state_name
              , dep_parent_ds.current_employee
              , 2 lvl
              FROM test.cp_departament dep
              JOIN test.cp_departament parent_dep ON parent_dep.id = dep.idparent
              JOIN test.cp_depart_state dep_parent_ds ON dep_parent_ds.id = parent_dep.boss_state
        ) boss_state ON boss_state.depart_id = states.state_dep_id AND boss_state.depart_boss_state_id != states.state_id                         
        JOIN test.cp_emp boss_emp ON boss_emp.id = boss_state.current_employee
        
        LEFT JOIN test.cp_files f1 ON f1.PARENTID = e.id AND f1.PARENT_TBL = 'userpic'
        LEFT JOIN test.cp_files f2 ON f2.PARENTID = boss_emp.id AND f2.PARENT_TBL = 'userpic'
        WHERE LOWER(e.name) LIKE LOWER('%'||:pSearch||'%')
        ORDER BY e.name
        SQL;
    }

    // https://docs.siberianhealth.com/x/XxFbCg
    private function customFilter(array $raw): array
    {
        $employeeLevels = [];
        foreach ($raw as $record) {
            $stateId = $record['states_id'];
            if (!isset($employeeLevels[$stateId])) {
                $employeeLevels[$stateId] = [];
            }
            $employeeLevels[$stateId][$record['boss_state_lvl']] = true;
        }
        return array_values(array_filter($raw, function (array $record) use ($employeeLevels): bool {
            $stateId = $record['states_id'];

            $hasBothLevels = isset($employeeLevels[$stateId]['1'], $employeeLevels[$stateId]['2']);
            // Условие для ИСКЛЮЧЕНИЯ (возвращаем false):
            // 1. У должности сотрудника есть оба уровня (1 и 2)
            // 2. И при этом текущая запись - это запись 2-го уровня
            return !($hasBothLevels && $record['boss_state_lvl'] === '2');
        }));
    }

    /**
     * @param iterable<array<string, mixed>> $raw
     * @return EmployeeSearchResponse[]
     */
    private function mapToEmployeeSearchResponse(iterable $raw): array
    {
        $data = iterator_to_array($raw);
        $data = $this->customFilter($data);

        $results = [];
        /** @var array<string, string> $row */
        foreach ($data as $row) {
            $userpicId = (int) $row['emp_userpic_id'];
            $bosspicId = (int) $row['boss_userpic_id'];

            $results[] = new EmployeeSearchResponse(
                id: (int) $row['id'],
                name: $row['emp_name'],
                isActive: (bool) $row['emp_is_active'],
                isTechnical: (bool) $row['emp_is_technical'],
                hasUuId: (bool) $row['emp_has_uu_id'],
                kpi: new EmployeeSearchKpiResponse(
                    hasNoKpi: (bool) $row['emp_has_no_kpi'],
                    hasKpi: (bool) $row['emp_has_kpi'],
                    hasTwoMonthKpi: (bool) $row['emp_has_two_month_kpi'],
                    hasFourMonthKpi: (bool) $row['emp_has_four_month_kpi'],
                    kpiLastChangeDate: isset($row['emp_kpi_last_change_date']) ? new DateTimeImmutable($row['emp_kpi_last_change_date']) : null,
                    hasSalaryUU: (bool) $row['emp_has_salary_uu'],
                ),
                position: new PositionResponse(
                    id: isset($row['states_id']) ? (int) $row['states_id'] : null,
                    name: $row['state_name'] ?? null,
                    isMain: $row['state_is_main'] === '1',
                    fromDate: isset($row['state_from_date']) ? new DateTimeImmutable($row['state_from_date']) : null,
                ),
                department: new DepartmentResponse(
                    name: $row['state_dep_name'] ?? null,
                    hasKpi: $row['state_dep_for_kpi'] === '1',
                    bossName: $row['emp_dep_boss_name'] ?? null,
                    bossPositionName: $row['boss_state_name'],
                    bossUserpic: $bosspicId !== 0 ? FileService::getCpFileUrlView($bosspicId) : null,
                ),
                userpic: $userpicId !== 0 ? FileService::getCpFileUrlView($userpicId) : null,
            );
        }
        return $results;
    }
}
