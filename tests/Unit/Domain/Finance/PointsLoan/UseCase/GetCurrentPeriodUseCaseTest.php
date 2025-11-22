<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\GetCurrentPeriodUseCase;

it('gets current period from repository', function (): void {
    // Arrange
    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $mockRepository = mock(LoanQueryRepository::class);
    $mockRepository->shouldReceive('getCurrentPeriod')
        ->andReturn($currentPeriod);

    $useCase = new GetCurrentPeriodUseCase($mockRepository);

    // Act
    $result = $useCase->getCurrentPeriod();

    // Assert
    expect($result)->toBe($currentPeriod);
    expect($result)->toBeInstanceOf(DateTimeImmutable::class);
});

it('calls repository once', function (): void {
    // Arrange
    $currentPeriod = new DateTimeImmutable('2024-01-01');

    $mockRepository = mock(LoanQueryRepository::class);
    $mockRepository->shouldReceive('getCurrentPeriod')
        ->once()
        ->andReturn($currentPeriod);

    $useCase = new GetCurrentPeriodUseCase($mockRepository);

    // Act
    $useCase->getCurrentPeriod();
});
