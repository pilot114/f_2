<?php

declare(strict_types=1);

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\EditLoanRequest;
use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Entity\LoanOperation;
use App\Domain\Finance\PointsLoan\Repository\GuarantorQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\EditLoanUseCase;
use Database\Connection\TransactionInterface;

beforeEach(function (): void {
    $this->loanQueryRepository = mock(LoanQueryRepository::class);
    $this->loanCommandRepository = mock(LoanCommandRepository::class);
    $this->loanOperationQueryRepository = mock(LoanOperationQueryRepository::class);
    $this->loanOperationCommandRepository = mock(LoanOperationCommandRepository::class);
    $this->guarantorQueryRepository = mock(GuarantorQueryRepository::class);
    $this->transaction = mock(TransactionInterface::class);

    $this->useCase = new EditLoanUseCase(
        $this->loanQueryRepository,
        $this->loanCommandRepository,
        $this->loanOperationQueryRepository,
        $this->loanOperationCommandRepository,
        $this->guarantorQueryRepository,
        $this->transaction
    );
});

it('edits loan successfully', function (): void {
    // Arrange
    $loanId = 123;
    $sum = 1500.0;
    $months = 18;
    $monthlyPayment = 83.33;
    $guarantorContract = 'GUAR123';

    $request = new EditLoanRequest($loanId, $sum, $months, $monthlyPayment, $guarantorContract);

    $currentPeriod = new DateTimeImmutable('2024-01-01');
    $startDate = new DateTimeImmutable('2024-01-01');
    $guarantor = new Guarantor(456, $guarantorContract);
    $loanOperation = mock(LoanOperation::class);

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(true);
    $mockLoan->shouldReceive('update')->with($sum, $months, $monthlyPayment, $guarantor);
    $mockLoan->shouldReceive('isMonthlyPaymentValid')->with($monthlyPayment)->andReturn(true);
    $mockLoan->shouldReceive('getAccrualOperationId')->andReturn(789);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    $this->guarantorQueryRepository->shouldReceive('getOneByContract')
        ->with($guarantorContract)
        ->andReturn($guarantor);

    $this->loanOperationQueryRepository->shouldReceive('findOrFail')
        ->with(789, 'не найдена операция создания займа с id = 789')
        ->andReturn($loanOperation);

    $loanOperation->shouldReceive('update')->with($sum);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanOperationCommandRepository->shouldReceive('update')->with($loanOperation)->once();
    $this->loanCommandRepository->shouldReceive('update')->with($mockLoan)->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $result = $this->useCase->edit($request);

    // Assert
    expect($result)->toBe($mockLoan);
});

it('edits loan without guarantor', function (): void {
    // Arrange
    $loanId = 123;
    $sum = 1500.0;
    $months = 18;
    $monthlyPayment = 83.33;

    $request = new EditLoanRequest($loanId, $sum, $months, $monthlyPayment, null);

    $currentPeriod = new DateTimeImmutable('2024-01-01');
    $startDate = new DateTimeImmutable('2024-01-01');
    $loanOperation = mock(LoanOperation::class);

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(true);
    $mockLoan->shouldReceive('update')->with($sum, $months, $monthlyPayment, null);
    $mockLoan->shouldReceive('isMonthlyPaymentValid')->with($monthlyPayment)->andReturn(true);
    $mockLoan->shouldReceive('getAccrualOperationId')->andReturn(789);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    $this->loanOperationQueryRepository->shouldReceive('findOrFail')
        ->with(789, 'не найдена операция создания займа с id = 789')
        ->andReturn($loanOperation);

    $loanOperation->shouldReceive('update')->with($sum);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanOperationCommandRepository->shouldReceive('update')->with($loanOperation)->once();
    $this->loanCommandRepository->shouldReceive('update')->with($mockLoan)->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $result = $this->useCase->edit($request);

    // Assert
    expect($result)->toBe($mockLoan);
});

it('throws exception when period is not current', function (): void {
    // Arrange
    $loanId = 123;
    $sum = 1500.0;
    $months = 18;
    $monthlyPayment = 83.33;

    $request = new EditLoanRequest($loanId, $sum, $months, $monthlyPayment);

    $currentPeriod = new DateTimeImmutable('2024-01-01');
    $startDate = new DateTimeImmutable('2024-02-01'); // Другой период

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(false);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    // Act & Assert
    expect(fn (): Loan => $this->useCase->edit($request))
        ->toThrow(InvariantDomainException::class, 'период выдачи займа отличается от текущего периода');
});

it('throws exception when monthly payment is invalid', function (): void {
    // Arrange
    $loanId = 123;
    $sum = 1500.0;
    $months = 18;
    $monthlyPayment = 100.0; // Неправильный платеж

    $request = new EditLoanRequest($loanId, $sum, $months, $monthlyPayment);

    $currentPeriod = new DateTimeImmutable('2024-01-01');
    $guarantor = new Guarantor(456, 'GUAR123');

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(true);
    $mockLoan->shouldReceive('update')->with($sum, $months, $monthlyPayment, null);
    $mockLoan->shouldReceive('isMonthlyPaymentValid')->with($monthlyPayment)->andReturn(false);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    // Act & Assert
    expect(fn (): Loan => $this->useCase->edit($request))
        ->toThrow(InvariantDomainException::class, 'передан неправильный ежемесячный платёж');
});
