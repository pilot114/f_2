<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetResponsibleUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('gets responsible by id', function (): void {
    // Arrange
    $responsibleId = 123;
    $mockResponsible = Mockery::mock(KpiResponsible::class);

    $mockRepository = Mockery::mock(KpiResponsibleQueryRepository::class);
    $mockRepository->shouldReceive('getResponsible')
        ->once()
        ->with($responsibleId)
        ->andReturn($mockResponsible);

    $useCase = new GetResponsibleUseCase($mockRepository);

    // Act
    $result = $useCase->get($responsibleId);

    // Assert
    expect($result)->toBe($mockResponsible);
});

it('throws exception when responsible not found', function (): void {
    // Arrange
    $responsibleId = 999;
    $errorMessage = "Не найден ответственный с id = $responsibleId";

    $mockRepository = Mockery::mock(KpiResponsibleQueryRepository::class);
    $mockRepository->shouldReceive('getResponsible')
        ->once()
        ->with($responsibleId)
        ->andThrow(new NotFoundHttpException($errorMessage));

    $useCase = new GetResponsibleUseCase($mockRepository);

    // Act & Assert
    expect(fn (): KpiResponsible => $useCase->get($responsibleId))
        ->toThrow(NotFoundHttpException::class, $errorMessage);
});

afterEach(function (): void {
    Mockery::close();
});
