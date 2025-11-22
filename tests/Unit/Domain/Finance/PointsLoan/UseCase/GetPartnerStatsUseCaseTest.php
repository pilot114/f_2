<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Entity\Country;
use App\Domain\Finance\PointsLoan\Entity\Partner;
use App\Domain\Finance\PointsLoan\Repository\PartnerQueryRepository;
use App\Domain\Finance\PointsLoan\UseCase\GetPartnerStatsUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('gets partner stats by contract', function (): void {
    // Arrange
    $contract = 'TEST123';
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 123,
        contract: $contract,
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );

    $mockRepository = mock(PartnerQueryRepository::class);
    $mockRepository->shouldReceive('getPartnerStats')
        ->with($contract)
        ->andReturn($partner);
    $mockRepository->shouldReceive('getPartnerEmails')
        ->with(123)
        ->andReturn(['test@example.com']);

    $useCase = new GetPartnerStatsUseCase($mockRepository);

    // Act
    $result = $useCase->getPartnerStats($contract);

    // Assert
    expect($result)->toBe($partner);
    expect($result->getEmailsAsString())->toBe('test@example.com');
});

it('throws exception when partner not found', function (): void {
    // Arrange
    $contract = 'INVALID999';
    $errorMessage = "не найден партнёр с контрактом $contract";

    $mockRepository = mock(PartnerQueryRepository::class);
    $mockRepository->shouldReceive('getPartnerStats')
        ->with($contract)
        ->andThrow(new NotFoundHttpException($errorMessage));

    $useCase = new GetPartnerStatsUseCase($mockRepository);

    // Act & Assert
    expect(fn (): Partner => $useCase->getPartnerStats($contract))
        ->toThrow(NotFoundHttpException::class, $errorMessage);
});

it('calls repository with correct contract', function (): void {
    // Arrange
    $contract = 'CONTRACT456';
    $country = new Country(1, 'USA');
    $partner = new Partner(
        id: 456,
        contract: $contract,
        name: 'Test Partner',
        country: $country,
        startDate: new DateTimeImmutable('2024-01-01')
    );

    $mockRepository = mock(PartnerQueryRepository::class);
    $mockRepository->shouldReceive('getPartnerStats')
        ->with($contract)
        ->once()
        ->andReturn($partner);
    $mockRepository->shouldReceive('getPartnerEmails')
        ->with(456)
        ->andReturn([]);

    $useCase = new GetPartnerStatsUseCase($mockRepository);

    // Act
    $result = $useCase->getPartnerStats($contract);

    // Assert
    expect($result)->toBe($partner);
    expect($result->getEmailsAsString())->toBe('');
});
