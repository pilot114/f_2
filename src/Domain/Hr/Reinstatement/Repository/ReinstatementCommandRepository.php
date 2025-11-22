<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\Repository;

use App\Common\Helper\RandomHelper;
use App\Domain\Hr\Reinstatement\Entity\Employee;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;

/**
 * @extends CommandRepository<Employee>
 */
class ReinstatementCommandRepository extends CommandRepository
{
    protected string $entityName = Employee::class;

    public function reinstateEmployee(int $employeeId, int $editorId): int
    {
        $dateTime = new DateTimeImmutable();
        return $this->conn->update('TEST.CP_EMP', [
            'ACTIVE'       => 'Y',
            'DATE_UPDATE'  => $dateTime,
            'IDEMP_UPDATE' => $editorId,
            'PW'           => RandomHelper::generateUserPassword(),
        ], [
            'ID' => $employeeId,
        ], [
            'ACTIVE'       => ParamType::STRING,
            'DATE_UPDATE'  => ParamType::DATE,
            'IDEMP_UPDATE' => ParamType::INTEGER,
            'PW'           => ParamType::STRING,
        ]);
    }

}
