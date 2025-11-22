<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\DeleteLoanRequest;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use Database\Connection\TransactionInterface;

class DeleteLoanUseCase
{
    public function __construct(
        private LoanQueryRepository $loanQueryRepository,
        private LoanCommandRepository $loanCommandRepository,
        private LoanOperationQueryRepository $loanOperationQueryRepository,
        private LoanOperationCommandRepository $loanOperationCommandRepository,
        private TransactionInterface $transaction,
    ) {
    }

    public function delete(DeleteLoanRequest $request): bool
    {
        $currentPeriod = $this->loanQueryRepository->getCurrentPeriod();

        $loan = $this->loanQueryRepository->findOrFail($request->loanId, 'не найден заём с id ' . $request->loanId);

        if (!$loan->isStartDateInCurrentPeriod($currentPeriod)) {
            throw new InvariantDomainException('период выдачи займа отличается от текущего периода');
        }

        $loanOperation = $this->loanOperationQueryRepository->findOrFail(
            $loan->getAccrualOperationId(),
            'не найдена операция создания займа с id = ' . $loan->getAccrualOperationId()
        );

        $this->transaction->beginTransaction();
        $this->loanCommandRepository->delete($loan->getId());
        $this->loanOperationCommandRepository->delete($loanOperation->getId());
        $this->transaction->commit();

        return true;
    }
}
