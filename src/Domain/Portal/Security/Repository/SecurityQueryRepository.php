<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Repository;

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Enum\AccessType;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<SecurityUser>
 */
class SecurityQueryRepository extends QueryRepository
{
    protected string $entityName = SecurityUser::class;

    public function findOneWithRoles(int $empId): ?SecurityUser
    {
        $sql = <<<SQL
        SELECT DISTINCT
            ce.id id,
            ce.name name,
            ce.email email,
            r.id roles_id,
            r.name roles_name
        FROM test.cp_emp ce
        JOIN acl.v_users_in_roles a ON a.user_id = id
        JOIN acl.roles r ON r.id = a.role_id
        WHERE ce.id = :id
        SQL;

        return $this->query($sql, [
            'id' => $empId,
        ])->first();
    }

    public function findOneWithPermissions(int $empId): ?SecurityUser
    {
        $sql = <<<SQL
        SELECT
          DISTINCT ce.id id,
          ce.name name,
          ce.email email,
          a.t || '_' || a.tt || '_' || a.T3 permissions_id,
          a.tt permissions_type,
          a.ttn permissions_name,
          a.access_type permissions_access_type,
          a.resource_id permissions_resource_id,
          a.resource_type permissions_resource_type
        FROM
          test.cp_emp ce
          JOIN acl.v_access a ON a.user_id = ce.id
        WHERE
          ce.id = :id
        SQL;

        return $this->query($sql, [
            'id' => $empId,
        ])->first();
    }

    /**
     * Универсально, для любого ресурса, но требует id ресурса
     * Пример:
     * hasPermission(empId: 42, resourceType: 'cp_action', resourceId: 1990)
     *
     * hasPermission(empId: 42, resourceType: 'rep_report', resourceId: 1990)
     */
    public function hasPermission(int $empId, string $resourceType, int $resourceId): bool
    {
        $sql = <<<SQL
        SELECT
            a.resource_id id,
            a.resource_type type
        FROM test.cp_emp ce
        JOIN acl.v_access a ON a.user_id = ce.id
        WHERE ce.id = :id
        AND a.resource_type = :resourceType AND a.resource_id = :resourceId
        SQL;
        $raw = $this->conn->query($sql, [
            'id'           => $empId,
            'resourceType' => $resourceType,
            'resourceId'   => $resourceId,
        ]);
        return iterator_count($raw) > 0;
    }

    public function hasCpMenu(int $empId, string $code): bool
    {
        $sql = <<<SQL
        SELECT
            1
        FROM acl.v_access a
        JOIN test.cp_departament_part cdp ON cdp.id = a.RESOURCE_ID 
        WHERE a.RESOURCE_TYPE = 'cp_menu'
        AND a.ACCESS_TYPE = 'read'
        AND a.user_id = :id
        AND cdp.ALIASNAME = :code
        SQL;
        $raw = $this->conn->query($sql, [
            'id'   => $empId,
            'code' => $code,
            'type' => AccessType::READ->value,
        ]);
        return iterator_count($raw) > 0;
    }

    public function hasCpAction(int $empId, string $code): bool
    {
        $sql = <<<SQL
        SELECT
            1
        FROM acl.v_access a
        JOIN test.acl_resource_item ri ON ri.id = a.resource_id
        JOIN test.acl_resource r ON ri.resource_id = r.id
        LEFT JOIN acl.full_access_exception fae ON fae.resource_id = a.RESOURCE_ID AND fae.RESOURCE_TYPE = a.RESOURCE_TYPE
        WHERE a.user_id = :id
        AND r.code || '.' || ri.code = :code
        AND a.access_type = :type
        AND (
            -- обычный доступ
            fae.resource_id is null
            OR
            -- иначе прямой доступ, игнорируем роль "полный доступ"
            (a.role_id <> 44 OR a.role_id is null)
        )
        and a.RESOURCE_TYPE = 'cp_action'
        SQL;
        $raw = $this->conn->query($sql, [
            'id'   => $empId,
            'code' => $code,
            'type' => AccessType::EXECUTE->value,
        ]);
        return iterator_count($raw) > 0;
    }

    /**
     * TODO: вынести методы, относящиеся к структуре компании, в отдельный repo
     */
    public function isDepartmentBoss(int $empId): bool
    {
        $sql = <<<SQL
        select
            1
            from test.cp_departament d
            join test.cp_depart_state ds on ds.id = d.boss_state
            join test.cp_emp e on e.id = ds.current_employee
            where e.id = :id and d.idparent in (2761, 2762)
        SQL;
        $raw = $this->conn->query($sql, [
            'id' => $empId,
        ]);
        return iterator_count($raw) > 0;
    }

    public function getDepartmentNameWhereBoss(int $empId): string
    {
        $sql = <<<SQL
        select
            d.name
            from test.cp_departament d
            join test.cp_depart_state ds on ds.id = d.boss_state
            join test.cp_emp e on e.id = ds.current_employee
            where e.id = :id
        SQL;
        $raw = $this->conn->query($sql, [
            'id' => $empId,
        ]);
        return iterator_to_array($raw)[0]['name'] ?? '';
    }

    public function findEmailByDepartStateId(int $departStateId): string
    {
        $sql = <<<SQL
        SELECT
            ce.email email
        FROM test.cp_emp ce
        JOIN test.CP_DEPART_STATE cds ON cds.CURRENT_EMPLOYEE = ce.id
        WHERE cds.id = :id
        SQL;

        $raw = $this->conn->query($sql, [
            'id' => $departStateId,
        ]);
        return iterator_to_array($raw)[0]['email'] ?? '';
    }
}
