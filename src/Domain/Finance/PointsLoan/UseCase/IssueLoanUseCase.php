<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\IssueLoanRequest;
use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Entity\LoanOperation;
use App\Domain\Finance\PointsLoan\Repository\GuarantorQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\PartnerQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;

class IssueLoanUseCase
{
    public function __construct(
        private LoanCommandRepository          $loanCommandRepository,
        private LoanOperationCommandRepository $loanOperationCommandRepository,
        private PartnerQueryRepository         $partnerQueryRepository,
        private GuarantorQueryRepository       $guarantorQueryRepository,
        private LoanQueryRepository            $LoanQueryRepository,
        private TransactionInterface           $transaction
    ) {
    }

    public function issueLoan(IssueLoanRequest $request): Loan
    {
        $this->partnerQueryRepository->existsOrFail($request->partnerId, 'не найден действующий партнер с id ' . $request->partnerId);

        $guarantor = $request->guarantor ? $this->guarantorQueryRepository->getOneByContract($request->guarantor) : null;

        $loan = $this->makeLoanObject($request, $guarantor);

        $currentPeriod = $this->LoanQueryRepository->getCurrentPeriod();
        if (!$loan->isStartDateInCurrentPeriod($currentPeriod)) {
            throw new InvariantDomainException('передан неправильный период выдачи займа');
        }

        if (!$loan->isMonthlyPaymentValid($request->monthlyPayment)) {
            throw new InvariantDomainException('передан неправильный ежемесячный платёж');
        }

        $loanOperation = $this->makeLoanOperationObject($request);

        $this->transaction->beginTransaction();
        $this->loanOperationCommandRepository->create($loanOperation);
        $loan->setAccrualOperationId($loanOperation->getId());
        $this->loanCommandRepository->create($loan);
        $this->transaction->commit();

        return $loan;
    }

    private function makeLoanOperationObject(IssueLoanRequest $request): LoanOperation
    {
        return new LoanOperation(
            id: Loader::ID_FOR_INSERT,
            ds: $request->period,
            de: $request->period,
            emp_spis: 16167,
            emp_nach: $request->partnerId,
            emp_buy: $request->partnerId,
            spistype: 7,
            lo: $request->sum,
            prim: "Балловый займ",
            curr: 1,
            sum: 0,
            kommb: 0,
            sum_native: 0,
            kommb_native: 0
        );
    }

    private function makeLoanObject(IssueLoanRequest $request, ?Guarantor $guarantor = null): Loan
    {
        return new Loan(
            id: Loader::ID_FOR_INSERT,
            accrualOperationId: Loader::ID_FOR_INSERT,
            partnerId: $request->partnerId,
            startDate: $request->period,
            sum: $request->sum,
            months: $request->months,
            monthlyPayment: $request->monthlyPayment,
            endDate: null,
            linkedLoanId: null,
            guarantor: $guarantor,
        );
    }
}
