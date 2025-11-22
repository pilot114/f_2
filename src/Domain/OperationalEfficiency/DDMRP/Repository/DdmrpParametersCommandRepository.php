<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<DdmrpParameters> */
class DdmrpParametersCommandRepository extends CommandRepository
{
    protected string $entityName = DdmrpParameters::class;
}
