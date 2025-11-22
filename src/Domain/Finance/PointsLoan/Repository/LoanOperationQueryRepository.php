<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\LoanOperation;
use Database\ORM\QueryRepository;

/** @extends QueryRepository<LoanOperation> */
class LoanOperationQueryRepository extends QueryRepository
{
    protected string $entityName = LoanOperation::class;
}
