<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Repository;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<Cok> */
class CokCommandRepository extends CommandRepository
{
    protected string $entityName = Cok::class;
}
