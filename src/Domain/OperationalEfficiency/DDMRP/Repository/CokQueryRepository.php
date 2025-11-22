<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Common\DTO\FilterOption;
use App\Domain\OperationalEfficiency\DDMRP\DTO\GetCokListRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use Database\Connection\ParamType;
use Database\ORM\Attribute\Loader;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @extends QueryRepository<Cok> */
class CokQueryRepository extends QueryRepository
{
    protected string $entityName = Cok::class;

    /** @return Enumerable<int, Cok> */
    public function getCokList(GetCokListRequest $request): Enumerable
    {
        $countryCondition = $this->resolveCountryCondition();
        $regionDirectorCondition = $this->resolveRegionDirectorCondition($request->regionDirectorId);
        $searchCondition = $this->resolveSearchCondition($request->search);

        $sql = $this->getCommonSql(
            countryCondition: $countryCondition,
            regionDirectorCondition: $regionDirectorCondition,
            searchCondition: $searchCondition
        );

        $cokList = $this->query(
            $sql,
            [
                'regionDirectorId' => $request->regionDirectorId,
                'country_id'       => $request->countryId,
                'search'           => $request->search,
            ]
        );

        // Сотрудников берем отдельным запросом
        $departmentIds = $cokList->pluck('departmentId')->toArray();
        $employeesByDepartment = $this
            ->getCokEmployees($departmentIds)
            ->groupBy('departmentId')
            ->toArray();

        foreach ($cokList as $cok) {
            if (isset($employeesByDepartment[$cok->departmentId]) && is_array($employeesByDepartment[$cok->departmentId])) {
                $cok->addEmployees($employeesByDepartment[$cok->departmentId]);
            }
        }

        return $cokList;
    }

    public function getCokByContract(string $contract): Cok
    {
        $countryCondition = '';
        $regionDirectorCondition = '';
        $searchCondition = "AND LOWER(ci.contract) = LOWER(:search)";

        $sql = $this->getCommonSql(
            countryCondition: $countryCondition,
            regionDirectorCondition: $regionDirectorCondition,
            searchCondition: $searchCondition
        );

        $collection = $this->query($sql, [
            'search' => $contract,
        ]);

        $cok = $collection->first();

        if (is_null($cok)) {
            throw new NotFoundHttpException("не найден ЦОК с контрактом = $contract");
        }

        $cokEmployees = $this->getCokEmployees([$cok->departmentId]);
        $cok->addEmployees($cokEmployees->toArray());

        return $cok;
    }

    private function getCommonSql(string $countryCondition, string $regionDirectorCondition, string $searchCondition): string
    {
        $idForInsert = Loader::ID_FOR_INSERT;
        return <<<SQL
        SELECT
        ci.id
        , d.id department_id
        , ci.contract contract
        , s.name name
        , ci.rd_id regional_director_id
        , reg.name regional_director_name
        , ci.grand_manager manager_id
        , manager.name manager_name
        , ci.for_ddmrp_calc 
        , s.address address
        , s.phone phone
        , s.email email
        , NVL(dcp.id, $idForInsert) ddmrp_parameters_id   
        , ci.contract ddmrp_parameters_contract  
        , dcp.dvf ddmrp_parameters_dvf
        , dcp.dltf ddmrp_parameters_dltf
        , dcp.dlt ddmrp_parameters_dlt
        , dcp.re_order_point ddmrp_parameters_re_order_point
        , dcp.expiration_percent ddmrp_parameters_expiration_percent
        , dcp.moq ddmrp_parameters_moq
        , dcp.slt ddmrp_parameters_slt
        
        FROM test.cp_cok_info ci
        JOIN test.cp_departament d ON d.id = ci.iddep
        JOIN tehno.sklads s ON s.type || s.contract = ci.contract
        LEFT JOIN test.cp_emp reg ON reg.id = ci.rd_id
        LEFT JOIN test.cp_emp manager ON manager.id = ci.grand_manager
        LEFT JOIN tehno.ddmrp_cok_parameters dcp ON dcp.contract = ci.contract
        
        WHERE ( s.close = 'N' OR ci.for_ddmrp_calc = 1 )
        $countryCondition
        $regionDirectorCondition
        $searchCondition
        ORDER BY ci.contract
        SQL;
    }

    /** @return Enumerable<int, CokEmployee> */
    private function getCokEmployees(array $departmentsIds): Enumerable
    {
        $departmentCondition = $this->resolveDepartmentCondition($departmentsIds);

        $sql = <<<SQL
        SELECT DISTINCT
          t.id
        , ci.contract cok_contract
        , t.depart department_id
        , emp.name name
        , j.id response_id
        , j.name response_name
        , emp.email email  
        , emp.mobil_phone phone
        , dce.id access_to_ddmrp_id
        , dce.contract access_to_ddmrp_contract
        , dce.cp_emp_id access_to_ddmrp_cp_emp_id
        
        FROM ( SELECT
                 ds.current_employee id
               , ds.depart
               FROM test.cp_depart_state ds
               WHERE SYSDATE BETWEEN ds.from_date AND ds.to_date
               UNION 
               SELECT 
                 e.id
               , e.iddepartament depart
               FROM test.cp_emp e      ) t
        JOIN test.cp_emp emp ON emp.id = t.id AND emp.active = 'Y'
        LEFT JOIN test.cp_emp_job_ref j ON j.id = emp.job_id
        JOIN test.cp_cok_info ci ON ci.iddep = t.depart
        LEFT JOIN tehno.ddmrp_cok_employers dce ON dce.contract = ci.contract AND emp.id = dce.cp_emp_id
        WHERE emp.id IN ( SELECT 
                          e1.id
                          FROM test.cp_emp e1
                          JOIN test.cp_emp_job_ref j1 ON j1.id = e1.job_id -- AND j1.for_ddmrp = 1
                          UNION ALL
                          SELECT 
                          dce1.cp_emp_id
                          FROM tehno.ddmrp_cok_employers dce1 )
        $departmentCondition
        ORDER BY emp.name
        SQL;

        $raw = $this->conn->query($sql, [
            'department_id_list' => $departmentsIds,
        ], [
            'department_id_list' => ParamType::ARRAY_INTEGER,
        ]);
        return $this->customDenormalizeToCollection($raw, CokEmployee::class);
    }

    private function resolveCountryCondition(): string
    {
        return "AND s.country_id = :country_id";
    }

    private function resolveRegionDirectorCondition(FilterOption|int $regionDirectorId): string
    {
        if ($regionDirectorId === FilterOption::Q_ANY) {
            return '';
        }

        if ($regionDirectorId === FilterOption::Q_SOME) {
            return 'AND ci.rd_id IS NOT NULL';
        }

        if ($regionDirectorId === FilterOption::Q_NONE) {
            return 'AND ci.rd_id IS NULL';
        }

        return 'AND ci.rd_id = :regionDirectorId';
    }

    private function resolveSearchCondition(?string $search): string
    {
        if ($search === null || $search === '') {
            return '';
        }

        return "AND LOWER(ci.contract) LIKE LOWER('%' || :search || '%')";
    }

    private function resolveDepartmentCondition(array $departmentIdList): string
    {
        if ($departmentIdList === []) {
            return '';
        }

        return 'AND t.depart IN ( :department_id_list )';
    }
}
