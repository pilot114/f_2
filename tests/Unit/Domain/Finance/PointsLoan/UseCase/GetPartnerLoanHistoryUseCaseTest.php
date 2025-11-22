<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Loan;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\GetPartnerLoanHistoryUseCase;
use Illuminate\Support\Enumerable;

it('gets partner loan history by partner id', function (): void {
    // Arrange
    $partnerId = 123;
    $mockLoan1 = mock(Loan::class);
    $mockLoan2 = mock(Loan::class);
    $loans = collect([$mockLoan1, $mockLoan2]);

    $mockRepository = mock(LoanQueryRepository::class);
    $mockRepository->shouldReceive('getHistory')
        ->with($partnerId)
        ->andReturn($loans);

    $useCase = new GetPartnerLoanHistoryUseCase($mockRepository);

    // Act
    $result = $useCase->getHistory($partnerId);

    // Assert
    expect($result)->toBeInstanceOf(Enumerable::class);
    expect($result)->toHaveCount(2);
    expect($result->first())->toBe($mockLoan1);
    expect($result->last())->toBe($mockLoan2);
});

it('returns empty collection when partner has no loans', function (): void {
    // Arrange
    $partnerId = 999;
    $emptyCollection = collect([]);

    $mockRepository = mock(LoanQueryRepository::class);
    $mockRepository->shouldReceive('getHistory')
        ->with($partnerId)
        ->andReturn($emptyCollection);

    $useCase = new GetPartnerLoanHistoryUseCase($mockRepository);

    // Act
    $result = $useCase->getHistory($partnerId);

    // Assert
    expect($result)->toBeInstanceOf(Enumerable::class);
    expect($result)->toHaveCount(0);
});

it('calls repository with correct partner id', function (): void {
    // Arrange
    $partnerId = 456;

    $mockRepository = mock(LoanQueryRepository::class);
    $mockRepository->shouldReceive('getHistory')
        ->with($partnerId)
        ->once()
        ->andReturn(collect([mock(Loan::class)]));

    $useCase = new GetPartnerLoanHistoryUseCase($mockRepository);

    // Act
    $useCase->getHistory($partnerId);
});

afterEach(function (): void {
    Mockery::close();
});
