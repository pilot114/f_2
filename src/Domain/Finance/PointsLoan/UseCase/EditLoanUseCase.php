<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\EditLoanRequest;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Repository\GuarantorQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use Database\Connection\TransactionInterface;

class EditLoanUseCase
{
    public function __construct(
        private LoanQueryRepository $loanQueryRepository,
        private LoanCommandRepository $loanCommandRepository,
        private LoanOperationQueryRepository $loanOperationQueryRepository,
        private LoanOperationCommandRepository $loanOperationCommandRepository,
        private GuarantorQueryRepository $guarantorQueryRepository,
        private TransactionInterface $transaction,
    ) {
    }

    public function edit(EditLoanRequest $request): Loan
    {
        $currentPeriod = $this->loanQueryRepository->getCurrentPeriod();

        $loan = $this->loanQueryRepository->findOrFail($request->loanId, 'не найден заём с id ' . $request->loanId);

        if (!$loan->isStartDateInCurrentPeriod($currentPeriod)) {
            throw new InvariantDomainException('период выдачи займа отличается от текущего периода');
        }

        $guarantor = $request->guarantor ? $this->guarantorQueryRepository->getOneByContract($request->guarantor) : null;

        $loan->update($request->sum, $request->months, $request->monthlyPayment, $guarantor);

        if (!$loan->isMonthlyPaymentValid($request->monthlyPayment)) {
            throw new InvariantDomainException('передан неправильный ежемесячный платёж');
        }

        $loanOperation = $this->loanOperationQueryRepository->findOrFail(
            $loan->getAccrualOperationId(),
            'не найдена операция создания займа с id = ' . $loan->getAccrualOperationId()
        );

        $this->transaction->beginTransaction();
        $loanOperation->update($request->sum);
        $this->loanOperationCommandRepository->update($loanOperation);
        $this->loanCommandRepository->update($loan);
        $this->transaction->commit();

        return $loan;
    }
}
