<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpEmployeeAccess;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<DdmrpEmployeeAccess> */
class DdmrpEmployeeAccessCommandRepository extends CommandRepository
{
    protected string $entityName = DdmrpEmployeeAccess::class;
}
