<?php

declare(strict_types=1);

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\PointsLoan\DTO\DeleteLoanRequest;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Entity\LoanOperation;
use App\Domain\Finance\PointsLoan\Repository\LoanCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationCommandRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanOperationQueryRepository;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\DeleteLoanUseCase;
use Database\Connection\TransactionInterface;

beforeEach(function (): void {
    $this->loanQueryRepository = mock(LoanQueryRepository::class);
    $this->loanCommandRepository = mock(LoanCommandRepository::class);
    $this->loanOperationQueryRepository = mock(LoanOperationQueryRepository::class);
    $this->loanOperationCommandRepository = mock(LoanOperationCommandRepository::class);
    $this->transaction = mock(TransactionInterface::class);

    $this->useCase = new DeleteLoanUseCase(
        $this->loanQueryRepository,
        $this->loanCommandRepository,
        $this->loanOperationQueryRepository,
        $this->loanOperationCommandRepository,
        $this->transaction
    );
});

it('deletes loan successfully', function (): void {
    // Arrange
    $loanId = 123;
    $request = new DeleteLoanRequest($loanId);

    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(true);
    $mockLoan->shouldReceive('getAccrualOperationId')->andReturn(789);
    $mockLoan->shouldReceive('getId')->andReturn($loanId);

    $mockLoanOperation = mock(LoanOperation::class);
    $mockLoanOperation->shouldReceive('getId')->andReturn(789);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    $this->loanOperationQueryRepository->shouldReceive('findOrFail')
        ->with(789, 'не найдена операция создания займа с id = 789')
        ->andReturn($mockLoanOperation);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanCommandRepository->shouldReceive('delete')->with($loanId)->once();
    $this->loanOperationCommandRepository->shouldReceive('delete')->with(789)->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $result = $this->useCase->delete($request);

    // Assert
    expect($result)->toBe(true);
});

it('throws exception when period is not current', function (): void {
    // Arrange
    $loanId = 123;
    $request = new DeleteLoanRequest($loanId);

    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(false);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    // Act & Assert
    expect(fn (): bool => $this->useCase->delete($request))
        ->toThrow(InvariantDomainException::class, 'период выдачи займа отличается от текущего периода');
});

it('calls repositories in correct order', function (): void {
    // Arrange
    $loanId = 123;
    $request = new DeleteLoanRequest($loanId);

    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $mockLoan = mock(Loan::class);
    $mockLoan->shouldReceive('isStartDateInCurrentPeriod')->with($currentPeriod)->andReturn(true);
    $mockLoan->shouldReceive('getAccrualOperationId')->andReturn(789);
    $mockLoan->shouldReceive('getId')->andReturn($loanId);

    $mockLoanOperation = mock(LoanOperation::class);
    $mockLoanOperation->shouldReceive('getId')->andReturn(789);

    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('getCurrentPeriod')->andReturn($currentPeriod);
    $this->loanQueryRepository->shouldReceive('findOrFail')
        ->with($loanId, 'не найден заём с id ' . $loanId)
        ->andReturn($mockLoan);

    $this->loanOperationQueryRepository->shouldReceive('findOrFail')
        ->with(789, 'не найдена операция создания займа с id = 789')
        ->andReturn($mockLoanOperation);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->loanCommandRepository->shouldReceive('delete')->with($loanId)->once();
    $this->loanOperationCommandRepository->shouldReceive('delete')->with(789)->once();
    $this->transaction->shouldReceive('commit')->once();

    // Act
    $this->useCase->delete($request);
});
