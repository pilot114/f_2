<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\LoanOperation;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<LoanOperation> */
class LoanOperationCommandRepository extends CommandRepository
{
    protected string $entityName = LoanOperation::class;
}
