<?php

declare(strict_types=1);

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\IssueLoanRequest;
use App\Domain\Finance\PointsLoan\Entity\Guarantor;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Repository\GuarantorQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\PartnerQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\IssueLoanUseCase;
use Database\Connection\TransactionInterface;

beforeEach(function (): void {
    $this->loanCommandRepository = mock(LoanCommandRepository::class);
    $this->loanOperationCommandRepository = mock(LoanOperationCommandRepository::class);
    $this->partnerQueryRepository = mock(PartnerQueryRepository::class);
    $this->guarantorQueryRepository = mock(GuarantorQueryRepository::class);
    $this->loanQueryRepository = mock(LoanQueryRepository::class);
    $this->transaction = mock(TransactionInterface::class);

    $this->useCase = new IssueLoanUseCase(
        $this->loanCommandRepository,
        $this->loanOperationCommandRepository,
        $this->partnerQueryRepository,
        $this->guarantorQueryRepository,
        $this->loanQueryRepository,
        $this->transaction
    );
});

it('issues loan successfully', function (): void {
    // Arrange
    $partnerId = 123;
    $sum = 1000.0;
    $months = 12;
    $monthlyPayment = 83.33;
    $period = new DateTimeImmutable('2024-01-01');
    $guarantorContract = 'GUAR123';

    $request = new IssueLoanRequest($partnerId, $sum, $months, $monthlyPayment, $period, $guarantorContract);

    $guarantor = new Guarantor(456, $guarantorContract);
    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $this->partnerQueryRepository->shouldReceive('existsOrFail')
        ->with($partnerId, 'не найден действующий партнер с id ' . $partnerId);

    $this->guarantorQueryRepository->shouldReceive('getOneByContract')
        ->with($guarantorContract)
        ->andReturn($guarantor);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')
        ->andReturn($currentPeriod);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanOperationCommandRepository->shouldReceive('create')->once();
    $this->loanCommandRepository->shouldReceive('create')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $result = $this->useCase->issueLoan($request);

    // Assert
    expect($result)->toBeInstanceOf(Loan::class);
    expect($result->partnerId)->toBe($partnerId);
    expect($result->startDate)->toBe($period);
});

it('issues loan without guarantor', function (): void {
    // Arrange
    $partnerId = 123;
    $sum = 1000.0;
    $months = 12;
    $monthlyPayment = 83.33;
    $period = new DateTimeImmutable('2024-01-01');

    $request = new IssueLoanRequest($partnerId, $sum, $months, $monthlyPayment, $period, null);
    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $this->partnerQueryRepository->shouldReceive('existsOrFail')
        ->with($partnerId, 'не найден действующий партнер с id ' . $partnerId);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')
        ->andReturn($currentPeriod);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanOperationCommandRepository->shouldReceive('create')->once();
    $this->loanCommandRepository->shouldReceive('create')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $result = $this->useCase->issueLoan($request);

    // Assert
    expect($result)->toBeInstanceOf(Loan::class);
    expect($result->partnerId)->toBe($partnerId);
});

it('throws exception when period is not current', function (): void {
    // Arrange
    $partnerId = 123;
    $sum = 1000.0;
    $months = 12;
    $monthlyPayment = 83.33;
    $period = new DateTimeImmutable('2024-01-01');

    $request = new IssueLoanRequest($partnerId, $sum, $months, $monthlyPayment, $period);
    $currentPeriod = new DateTimeImmutable('2024-02-01');

    $this->partnerQueryRepository->shouldReceive('existsOrFail')
        ->with($partnerId, 'не найден действующий партнер с id ' . $partnerId);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')
        ->andReturn($currentPeriod);

    // Act & Assert
    expect(fn (): Loan => $this->useCase->issueLoan($request))
        ->toThrow(InvariantDomainException::class, 'передан неправильный период выдачи займа');
});

it('throws exception when monthly payment is invalid', function (): void {
    // Arrange
    $partnerId = 123;
    $sum = 1000.0;
    $months = 12;
    $monthlyPayment = 10000.0; // Неправильный платеж (должен быть 83.33)
    $period = new DateTimeImmutable('2024-01-01');

    $request = new IssueLoanRequest($partnerId, $sum, $months, $monthlyPayment, $period);
    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $this->partnerQueryRepository->shouldReceive('existsOrFail')
        ->with($partnerId, 'не найден действующий партнер с id ' . $partnerId);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')
        ->andReturn($currentPeriod);

    // Act & Assert
    expect(fn (): Loan => $this->useCase->issueLoan($request))
        ->toThrow(InvariantDomainException::class, 'передан неправильный ежемесячный платёж');
});
