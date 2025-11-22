<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use DateTimeInterface;
use Generator;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Kpi>
 */
class KpiQueryRepository extends QueryRepository
{
    protected string $entityName = Kpi::class;

    public function lastDateSend(int $empId): ?DateTimeInterface
    {
        $date = $this->conn->max('tehno.kpi_accured_history_log', 'log_ts', [
            'user_id'   => $empId,
            'is_sended' => 1,
        ]);
        return is_float($date) ? null : $date;
    }

    /**
     * @return Enumerable<int, KpiEmployee>
     */
    public function getBosses(?string $q = null): Enumerable
    {
        $sql = <<<SQL
        SELECT 
          emp.id AS id, 
          emp.name AS name, 
          CASE 
            WHEN f.parentid IS NOT NULL THEN 1 
            ELSE 0 
          END AS has_userpic, 
          -------------------- kpi
          kah.id AS kpi_id, 
          fe.id AS kpi_employee_id, 
          kah.dt AS kpi_dt, 
          kah.kpi_type AS kpi_kpi_type, 
          kah.kpi_value AS kpi_kpi_value, 
          kah.is_sended AS kpi_is_sended, 
          kah.dt_send AS kpi_dt_send, 
          --------------------
          dep.id AS positions_id, 
          dep.name AS positions_name, 
          case when depstr.boss_state = dep.id then 1 else 0 end positions_is_boss
        FROM 
          test.cp_departament depstr 
          LEFT JOIN test.cp_depart_state dep ON dep.id = depstr.boss_state
          JOIN test.cp_emp_state es ON es.state = dep.id AND es.employee = dep.current_employee AND es.is_main = 1 --фильтр по основной должности
          LEFT JOIN test.cp_emp emp ON emp.id = dep.current_employee 
            AND emp.active = 'Y' 
            AND emp.is_technical = 0 
          LEFT JOIN tehno.finemployee fe ON fe.cp_id = emp.id 
          JOIN tehno.kpi_accured_history kah ON kah.employee_id = fe.id 
            AND (
              kah.dt = add_months(trunc(sysdate, 'mm'), -1) 
              OR (
                kah.dt < add_months(trunc(sysdate, 'mm'), -1) 
                AND (kah.kpi_value IS NULL OR kah.is_sended = 0)
              )
            )
          LEFT JOIN test.cp_files f ON f.parentid = emp.id 
            AND f.parent_tbl = 'userpic' 
            AND f.is_on_static = 1 
        WHERE 
          depstr.is_closed = 0 
          AND depstr.for_kpi_s = 1 
          AND (kah.kpi_value IS NULL OR kah.kpi_value != 888)
          AND lower(emp.name) like '%' || lower(:pSearch) || '%'
        ORDER BY
          depstr.idparent ASC, 
          depstr.name ASC, 
          depstr.boss_state DESC, 
          emp.name ASC
        SQL;

        $raw = $this->conn->query($sql, [
            'pSearch' => $q,
        ]);
        return $this->customDenormalizeToCollection($raw, KpiEmployee::class);
    }

    /**
     * @return Enumerable<int, KpiDepartment>
     */
    public function getList(int $empId, ?string $q = null, bool $onlyBoss = false): Enumerable
    {
        $raw = $this->rawKpiList($empId, $q, $onlyBoss);

        /** @var Enumerable<int, KpiDepartment> $groupedByDeps */
        $groupedByDeps = $this->customDenormalizeToCollection($raw, KpiDepartment::class);

        return $this->addMissingParents($groupedByDeps);
    }

    /**
     * @param Enumerable<int, KpiDepartment> $groupedByDeps
     * @return Enumerable<int, KpiDepartment>
     */
    private function addMissingParents(Enumerable $groupedByDeps): Enumerable
    {
        $existingIds = [];
        foreach ($groupedByDeps as $obj) {
            $existingIds[$obj->getId()] = true;
        }

        $processedParents = [];

        $objectsToProcess = clone $groupedByDeps;

        while ($objectsToProcess->count() > 0) {
            /** @var KpiDepartment $currentObject */
            $currentObject = $objectsToProcess->shift();
            $parentId = $currentObject->getParentId();

            if ($parentId === 0) {
                continue;
            }
            if (isset($existingIds[$parentId])) {
                continue;
            }
            if (isset($processedParents[$parentId])) {
                continue;
            }
            $nextLevel = $currentObject->getLevel() - 1;

            if ($nextLevel < 1) {
                continue;
            }

            $parentObject = $this->searchDepartById($parentId, $nextLevel);

            $groupedByDeps->push($parentObject);
            $existingIds[$parentId] = true;
            $processedParents[$parentId] = true;

            $objectsToProcess->push($parentObject);
        }

        return $groupedByDeps;
    }

    private function searchDepartById(int $id, int $level): KpiDepartment
    {
        $sql = <<<SQL
            SELECT
                d.id,
                d.name,
                d.idparent
            FROM test.cp_departament d
            WHERE d.id = :id
        SQL;

        $item = $this->conn->query($sql, [
            'id' => $id,
        ])->current();

        return new KpiDepartment(
            id: (int) $item['id'],
            name: $item['name'],
            parentId: (int) $item['idparent'],
            level: $level,
        );
    }

    /**
     * @return Enumerable<int, Kpi>
     */
    public function getHistory(int $empId): Enumerable
    {
        $sql = <<<SQL
            SELECT
                h.id,
                h.employee_id,
                h.dt,
                h.kpi_type,
                h.kpi_value,
                h.kpi_value_calc,
                h.is_sended
            FROM tehno.kpi_accured_history h
            JOIN tehno.finemployee f ON h.employee_id = f.id
            JOIN tehno.fin_salary fs ON fs.employee = f.id
            where
                f.cp_id = :pUserId
                AND h.dt BETWEEN fs.FROM_DATE and fs.TO_DATE
            order by h.dt desc, h.kpi_type
        SQL;

        return $this->query($sql, [
            'pUserId' => $empId,
        ]);
    }

    /**
     * @return Enumerable<int, Kpi>
     */
    public function getHistoryWithMetrics(int $empId): Enumerable
    {
        $sql = <<<SQL
            select
                ------------------------------------------ kpi
                h.id,
                h.employee_id,
                h.dt,
                h.kpi_type,
                h.kpi_value,
                h.is_sended,
                r.id responsible,
                ------------------------------------------ привязка метрик к kpi
                hm.id                       history_id,
                hm.metric_name              history_metric_name,
                hm.plan_value               history_plan_value,
                hm.factual_value            history_factual_value,
                hm.weight                   history_weight,
                hm.calculation_description  history_calculation_description,
                hm.ranges_count             history_ranges_count,
                hm.ranges_description       history_ranges_description
            from tehno.kpi_accured_history h
            JOIN tehno.finemployee f ON h.employee_id = f.id
            LEFT JOIN tehno.kpi_accrued_history_metric hm on hm.kpi_accrued_history_id = h.id
            JOIN tehno.fin_salary fs ON fs.employee = f.id
            JOIN tehno.finclient fc ON fc.id = fs.enterprise 
            LEFT JOIN tehno.kpi_responsible r ON r.enterprise_id = fc.id
            where f.cp_id = :pUserId
                AND h.dt BETWEEN fs.FROM_DATE and fs.TO_DATE 
            order by h.dt desc, h.kpi_type
        SQL;

        return $this->query($sql, [
            'pUserId' => $empId,
        ]);
    }

    /**
     * @return array<int>
     */
    public function findEmpForExport(int $empId, ?string $q, bool $onlyBoss = false): array
    {
        $raw = $this->rawKpiList($empId, $q, $onlyBoss);
        $ids = array_column(iterator_to_array($raw), 'emps_kpi_employee_id');
        $uniqIds = array_values(array_unique($ids));
        return array_map(intval(...), $uniqIds);
    }

    public function bossListForExport(?string $q): array
    {
        return $this->getBosses($q)->keys()->toArray();
    }

    /**
     * @param $isLastPeriod:
     * false - только неотправленные
     * true  - помеченные как отправденные данные, только по последнему периоду
     */
    public function dataForExport(array $finEmpIds, bool $isLastPeriod = false): array
    {
        $filterBySended = 'h.is_sended = 0';
        if ($isLastPeriod) {
            $filterBySended = 'h.is_sended = 1';
        }

        $sql = <<<SQL
            SELECT distinct 
                e.id,
                fe.last_name,
                fe.first_name,
                fe.middle_name,
                cfo.contract cfo_contract,
                cfo.name cfo_name,
                kpi.kpi,
                kpi.two_month_bonus,
                kpi.four_months_bonus,
                enterprise.id enterprise_id,
                enterprise.name enterprise_name,
                ec.country_id enterprise_country,
                kpi.dt
            FROM
                tehno.finemployee fe
            JOIN tehno.fin_salary fs ON fs.employee = fe.id
            JOIN tehno.finclient cfo ON cfo.id = fs.costscenter
            LEFT JOIN tehno.finclient enterprise ON enterprise.id = fs.enterprise
            LEFT JOIN tehno.enterprise_country ec ON ec.enterprise_id = enterprise.id
            JOIN (
                SELECT
                    h.employee_id,
                    h.dt,
                    MIN(CASE WHEN h.kpi_type = 1 THEN h.kpi_value END) AS kpi,
                    MIN(CASE WHEN h.kpi_type = 2 THEN h.kpi_value END) AS two_month_bonus,
                    MIN(CASE WHEN h.kpi_type = 3 THEN h.kpi_value END) AS four_months_bonus
                FROM
                    tehno.kpi_accured_history h
                WHERE
                    $filterBySended
                    AND h.kpi_value NOT IN (888, 999)
                    AND h.kpi_value IS NOT NULL
                    GROUP BY h.employee_id, h.dt
                  ) kpi ON kpi.employee_id = fe.id
                AND kpi.dt <= add_months(trunc(sysdate, 'mm'),-1)
                AND kpi.dt BETWEEN fs.FROM_DATE and fs.TO_DATE 
            JOIN test.cp_emp e ON e.id = fe.cp_id
            WHERE fe.id IN (:pUserList)
        SQL;

        $raw = $this->conn->query($sql, [
            'pUserList' => $finEmpIds,
        ], [
            'pUserList' => ParamType::ARRAY_INTEGER,
        ]);
        return iterator_to_array($raw);
    }

    public function getResponsibleEmailsByEnterprises(array $enterpriseIds): array
    {
        $sql = <<<SQL
            SELECT
            r.enterprise_id, ce.email
            FROM TEHNO.KPI_RESPONSIBLE r
            JOIN test.cp_emp ce ON ce.id = r.user_id
            WHERE r.ENTERPRISE_ID IN (:enterpriseIds)
        SQL;
        $raw = $this->conn->query($sql, [
            'enterpriseIds' => $enterpriseIds,
        ], [
            'enterpriseIds' => ParamType::ARRAY_INTEGER,
        ]);
        $map = [];
        foreach ($raw as $item) {
            $map[(int) $item['enterprise_id']] = $item['email'];
        }
        return $map;
    }

    /**
     * Кого пользователь замещает в текущий момент
     */
    public function whoDeputied(int $empId): array
    {
        $sql = <<<SQL
            SELECT user_id
            FROM TEHNO.KPI_DEPUTY
            where deputy_user_id = :empId
            AND TRUNC(start_date) <= TRUNC(SYSDATE)
            AND TRUNC(end_date) >= TRUNC(SYSDATE)
        SQL;
        $raw = $this->conn->query($sql, [
            'empId' => $empId,
        ]);
        $raw = iterator_to_array($raw);
        $ids = array_column($raw, 'user_id');
        return array_map(intval(...), $ids);
    }

    public function getBossesIds(int $empId): array
    {
        $sql = <<<SQL
            SELECT
            e.id
            from 
            (--орг. структура подчинения  пользователя
                        SELECT
                            d.id,
                            d.name,
                            d.idparent,
                            LEVEL dep_level,
                            d.boss_state
                        FROM test.cp_departament d
                        WHERE d.is_closed = 0 -- AND d.for_kpi = 1
                        CONNECT BY d.id = PRIOR d.idparent
                        START WITH d.id = (SELECT e.iddepartament FROM test.cp_emp e WHERE e.id = :empId
            )
                    ) depstr 
            left join test.cp_departament d1 on d1.id = depstr.idparent
            left join test.cp_depart_state ds on ds.id = depstr.boss_state
            left join test.cp_emp e on e.id = ds.current_employee
            WHERE ds.current_employee IS NOT NULL AND e.active = 'Y'
        SQL;
        $raw = $this->conn->query($sql, [
            'empId' => $empId,
        ]);
        $raw = iterator_to_array($raw);
        $ids = array_column($raw, 'id');
        return array_map(intval(...), $ids);
    }

    /**
     * @return Generator<int, array>
     */
    private function rawKpiList(int $empId, ?string $q = null, bool $onlyBoss = false): iterable
    {
        $deputiedIds = $this->whoDeputied($empId);

        $empIds = [...$deputiedIds, $empId];

        // если замещаем своего руководителя - себя надо выводить
        if ($deputiedIds !== [] && array_intersect($deputiedIds, $this->getBossesIds($empId)) !== []) {
            $excludeUserIds = $deputiedIds;
        } else {
            $excludeUserIds = $empIds;
        }

        $sqlOnlyBoss = $onlyBoss ? 'and depstr.boss_state = kpi.deps_id' : '';

        $sql = <<<SQL
        select
            depstr.id,
            depstr.name,
            depstr.idparent,
            depstr.dep_level,
            --------------------
            kpi.emp_id              emps_id,
            kpi.emp_name            emps_name,
            CASE WHEN EXISTS (
              SELECT 1
              FROM test.cp_files f
              WHERE f.parentid = kpi.emp_id AND f.parent_tbl = 'userpic' AND f.is_on_static = 1
            ) THEN 1 ELSE 0
            END AS                  emps_has_userpic,
            --------------------
            kpi.kpi_id         emps_kpi_id,
            kpi.finemployee    emps_kpi_employee_id,
            kpi.kpi_dt         emps_kpi_dt,
            kpi.kpi_type       emps_kpi_kpi_type,
            kpi.kpi_value      emps_kpi_kpi_value,
            kpi.is_sended      emps_kpi_is_sended,
            kpi.dt_send        emps_kpi_dt_send,
            --------------------
            kpi.dep_state_id       emps_positions_id,
            kpi.dep_state_name     emps_positions_name,
            case when depstr.boss_state = kpi.deps_id then 1 else 0 end emps_positions_is_boss
        FROM
        (
            SELECT
                d.id,
                d.name,
                d.idparent,
                LEVEL dep_level,
                d.boss_state
            FROM test.cp_departament d
            WHERE d.is_closed = 0 AND d.for_kpi = 1
            CONNECT BY d.idparent = PRIOR d.id START WITH d.id = (
                SELECT e.iddepartament
                FROM test.cp_emp e
                WHERE e.id in (:pUserIds)
                and exists (select ds.* from test.cp_depart_state ds where ds.id = d.boss_state and ds.current_employee = e.id)
            )
        ) depstr
        LEFT JOIN
        (
            SELECT
            emp.id emp_id,
            emp.name emp_name,
            dep_state.id deps_id,
            dep_state.depart,
            dep_state.id dep_state_id,
            dep_state.name dep_state_name,
            kah.id kpi_id,
            kah.dt kpi_dt,
            kah.kpi_type,
            kah.kpi_value,
            kah.is_sended,
            kah.dt_send,
            fe.id finemployee
            FROM
            test.cp_depart_state dep_state
            JOIN test.cp_emp_state es ON es.state = dep_state.id AND es.employee = dep_state.current_employee AND es.is_main = 1 --фильтр по основной должности
            JOIN test.cp_emp emp ON emp.id = dep_state.current_employee
            AND emp.active = 'Y' AND emp.is_technical = 0 AND emp.id not in (:pExcludeUserIds) 
            JOIN tehno.finemployee fe ON fe.cp_id = emp.id
            JOIN tehno.kpi_accured_history kah ON kah.employee_id = fe.id
            JOIN tehno.fin_salary fs ON fs.employee = fe.id
            WHERE
                dep_state.current_employee IS NOT NULL
                AND kah.dt BETWEEN fs.FROM_DATE and fs.TO_DATE 
        ) kpi ON
        kpi.depart = depstr.id
        where
            (
                kpi.kpi_dt = add_months(trunc(sysdate, 'mm'),-1)
                or (kpi.kpi_dt < add_months(trunc(sysdate, 'mm'),-1) and (kpi.kpi_value is null or kpi.is_sended = 0))
            )
            and ( kpi.kpi_value is null or kpi.kpi_value != 888)
            and (lower(kpi.emp_name) like '%' || lower(:pSearch) || '%')
            $sqlOnlyBoss
        ORDER BY
        dep_level DESC, name ASC, emps_positions_is_boss DESC, emp_name ASC
        SQL;

        return $this->conn->query($sql, [
            'pUserIds'        => $empIds,
            'pExcludeUserIds' => $excludeUserIds,
            'pSearch'         => $q,
        ], [
            'pUserIds'        => ParamType::ARRAY_INTEGER,
            'pExcludeUserIds' => ParamType::ARRAY_INTEGER,
        ]);
    }

    /**
     * @return Enumerable<int, Deputy>
     */
    public function getDeputyList(int $userId): Enumerable
    {
        $sql = <<<SQL
            select
                d.id         id,
                d.user_id    user_id,
                d.start_date start_date,
                d.end_date   end_date,
                ------------------------------------------
                e.id    deputy_user_id_id,
                e.name  deputy_user_id_name,
                ------------------------------------------
                ds.id   deputy_user_id_positions_id,
                ds.name deputy_user_id_positions_name,
                ------------------------------------------
                dep.id    deputy_user_id_departments_id,
                dep.name  deputy_user_id_departments_name
            from tehno.kpi_deputy d
            join test.cp_emp e on e.id = d.deputy_user_id
            join test.cp_depart_state ds on ds.current_employee = e.id
            join test.cp_departament dep on ds.depart = dep.id
            where user_id = :userId
        SQL;
        $raw = $this->conn->query($sql, [
            'userId' => $userId,
        ]);
        return $this->customDenormalizeToCollection($raw, Deputy::class);
    }
}
