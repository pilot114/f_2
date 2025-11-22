<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Repository;

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Enum\AccessType;
use App\Domain\Portal\Security\Enum\ResourceType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<SecurityUser>
 */
class SecurityCommandRepository extends CommandRepository
{
    protected string $entityName = SecurityUser::class;

    public function revokeCpMenuAccess(int $employeeId, int $cpMenuId, AccessType $accessType): void
    {
        $params = [
            'i_resource_type_code' => ResourceType::CP_MENU->value,
            'i_resource_id'        => $cpMenuId,
            'i_resource_code'      => null,
            'i_access_type_code'   => $accessType->value,
            'i_user_id'            => $employeeId,
            'i_role_id'            => null,
        ];

        $this->conn->procedure('acl.pacl.delete_access', $params);
    }

    public function grantCpMenuAccess(int $employeeId, int $cpMenuId, AccessType $accessType): void
    {
        $params = [
            'i_resource_type_code' => 'cp_menu',
            'i_resource_id'        => $cpMenuId,
            'i_resource_code'      => null,
            'i_access_type_code'   => $accessType->value,
            'i_user_id'            => $employeeId,
            'i_role_id'            => null,
        ];

        $this->conn->procedure('acl.pacl.add_access', $params);
    }
}
