<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\Loan;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<Loan> */
class LoanCommandRepository extends CommandRepository
{
    protected string $entityName = Loan::class;
}
