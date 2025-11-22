<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/** @extends QueryRepository<Guarantor> */
class GuarantorQueryRepository extends QueryRepository
{
    protected string $entityName = Guarantor::class;

    public function getOneByContract(string $guarantorContract): Guarantor
    {
        $guarantor = $this->findOneBy([
            'contract' => $guarantorContract,
        ]);

        if (!($guarantor instanceof Guarantor) || !$guarantor->isActive()) {
            throw new EntityNotFoundDatabaseException('не найден действующий партнер-гарант с контрактом ' . $guarantorContract);
        }

        return $guarantor;
    }
}
