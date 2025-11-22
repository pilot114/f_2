<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\DTO\CreateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\DTO\CreateKpiRangeRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiRangeRequest;
use App\Domain\Finance\Kpi\Entity\KpiMetricRange;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Repository\KpiMetricTypeCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiRangesCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiRangesQueryRepository;
use App\Domain\Finance\Kpi\UseCase\WriteMetricTypeUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function (): void {
    $this->writeMetricTypeRepo = Mockery::mock(KpiMetricTypeCommandRepository::class);
    $this->readMetricTypeRepo = Mockery::mock(QueryRepositoryInterface::class);
    $this->writeRangesRepo = Mockery::mock(KpiRangesCommandRepository::class);
    $this->readRangesRepo = Mockery::mock(KpiRangesQueryRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new WriteMetricTypeUseCase(
        $this->writeMetricTypeRepo,
        $this->readMetricTypeRepo,
        $this->writeRangesRepo,
        $this->readRangesRepo,
        $this->transaction
    );
});

afterEach(function (): void {
    Mockery::close();
});

test('createMetricType creates a linear metric type successfully', function (): void {
    // Arrange
    $createRequest = new CreateKpiMetricTypeRequest(
        name: 'Test Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: []
    );

    $expectedMetricType = new KpiMetricType(
        id: 1,
        name: 'Test Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: [],
        isActive: true
    );

    // Expect transaction to be started and committed
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Expect repository to create metric type
    $this->writeMetricTypeRepo->shouldReceive('createMetricType')
        ->once()
        ->andReturn($expectedMetricType);

    // Act
    $result = $this->useCase->createMetricType($createRequest);

    // Assert
    expect($result)->toBeInstanceOf(KpiMetricType::class)
        ->and($result->getId())->toBe(1);
});

test('createMetricType creates a range-based metric type with ranges successfully', function (): void {
    // Arrange
    $createRequest = new CreateKpiMetricTypeRequest(
        name: 'Test Range Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [
            new CreateKpiRangeRequest(
                startPercent: 0,
                endPercent: 50,
                kpiPercent: 25
            ),
            new CreateKpiRangeRequest(
                startPercent: 51,
                endPercent: 100,
                kpiPercent: 75
            ),
        ]
    );

    $expectedMetricType = new KpiMetricType(
        id: 1,
        name: 'Test Range Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [],
        isActive: true
    );

    $expectedRange1 = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 50,
        kpiPercent: 25,
        metricTypeId: 1
    );

    $expectedRange2 = new KpiMetricRange(
        id: 2,
        startPercent: 51,
        endPercent: 100,
        kpiPercent: 75,
        metricTypeId: 1
    );

    // Expect transaction to be started and committed
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Expect repository to create metric type
    $this->writeMetricTypeRepo->shouldReceive('createMetricType')
        ->once()
        ->andReturn($expectedMetricType);

    // Expect range validation
    $this->readRangesRepo->shouldReceive('existInRange')
        ->twice()
        ->andReturn(false);

    // Expect repository to create ranges
    $this->writeRangesRepo->shouldReceive('addRange')
        ->twice()
        ->andReturn($expectedRange1, $expectedRange2);

    // Act
    $result = $this->useCase->createMetricType($createRequest);

    // Assert
    expect($result)->toBeInstanceOf(KpiMetricType::class)
        ->and($result->getId())->toBe(1);
});

test('createMetricType throws exception when ranges overlap', function (): void {
    // Arrange
    $createRequest = new CreateKpiMetricTypeRequest(
        name: 'Test Overlap Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [
            new CreateKpiRangeRequest(
                startPercent: 0,
                endPercent: 60,
                kpiPercent: 25
            ),
            new CreateKpiRangeRequest(
                startPercent: 50,
                endPercent: 100,
                kpiPercent: 75
            ),
        ]
    );

    $expectedMetricType = new KpiMetricType(
        id: 1,
        name: 'Test Overlap Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [],
        isActive: true
    );

    // Expect transaction to be started and rolled back
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->never();

    // Expect repository to create metric type
    $this->writeMetricTypeRepo->shouldReceive('createMetricType')
        ->once()
        ->andReturn($expectedMetricType);

    $this->writeRangesRepo->shouldReceive('addRange')
        ->once()
    ;

    // Expect range validation to fail for the second range
    $this->readRangesRepo->shouldReceive('existInRange')
        ->once()
        ->andReturn(false);
    $this->readRangesRepo->shouldReceive('existInRange')
        ->once()
        ->andReturn(true);

    // Act & Assert
    expect(fn () => $this->useCase->createMetricType($createRequest))
        ->toThrow(ConflictHttpException::class, 'Диапазон пересекается с уже существующим диапазоном');
});

test('updateMetricType updates name and isActive properties', function (): void {
    // Arrange
    $updateRequest = new UpdateKpiMetricTypeRequest(
        id: 1,
        name: 'Updated Metric',
        planType: null,
        ranges: [],
        isActive: false
    );

    $existingMetricType = new KpiMetricType(
        id: 1,
        name: 'Original Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: [],
        isActive: true
    );

    $updatedMetricType = new KpiMetricType(
        id: 1,
        name: 'Updated Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: [],
        isActive: false
    );

    // Expect transaction to be started and committed
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Expect repository to find and update metric type
    $this->readMetricTypeRepo->shouldReceive('findOrFail')
        ->once()
        ->with(1, 'Не найден тип метрики')
        ->andReturn($existingMetricType);

    $this->writeMetricTypeRepo->shouldReceive('updateMetricType')
        ->once()
        ->andReturn($updatedMetricType);

    // Act
    $result = $this->useCase->updateMetricType($updateRequest);

    // Assert
    expect($result)->toBeInstanceOf(KpiMetricType::class)
        ->and($result->getId())->toBe(1);
});

test('updateMetricType changes plan type and recreates ranges', function (): void {
    // Arrange
    $updateRequest = new UpdateKpiMetricTypeRequest(
        id: 1,
        name: null,
        planType: PaymentPlanType::RANGES,
        ranges: [
            new UpdateKpiRangeRequest(
                startPercent: 0,
                endPercent: 50,
                kpiPercent: 25
            ),
            new UpdateKpiRangeRequest(
                startPercent: 51,
                endPercent: 100,
                kpiPercent: 75
            ),
        ],
        isActive: null
    );

    $existingMetricType = new KpiMetricType(
        id: 1,
        name: 'Original Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: [],
        isActive: true
    );

    $updatedMetricType = new KpiMetricType(
        id: 1,
        name: 'Original Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [],
        isActive: true
    );

    $expectedRange1 = new KpiMetricRange(
        id: 1,
        startPercent: 0,
        endPercent: 50,
        kpiPercent: 25,
        metricTypeId: 1
    );

    $expectedRange2 = new KpiMetricRange(
        id: 2,
        startPercent: 51,
        endPercent: 100,
        kpiPercent: 75,
        metricTypeId: 1
    );

    // Expect transaction to be started and committed
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    // Expect repository to find and update metric type
    $this->readMetricTypeRepo->shouldReceive('findOrFail')
        ->once()
        ->with(1, 'Не найден тип метрики')
        ->andReturn($existingMetricType);

    $this->writeMetricTypeRepo->shouldReceive('updateMetricType')
        ->once()
        ->andReturn($updatedMetricType);

    // Expect ranges to be deleted and recreated
    $this->writeRangesRepo->shouldReceive('deleteRangesByMetricTypeId')
        ->once()
        ->with(1);

    // Expect range validation
    $this->readRangesRepo->shouldReceive('existInRange')
        ->twice()
        ->andReturn(false);

    // Expect repository to create ranges
    $this->writeRangesRepo->shouldReceive('addRange')
        ->twice()
        ->andReturn($expectedRange1, $expectedRange2);

    // Act
    $result = $this->useCase->updateMetricType($updateRequest);

    // Assert
    expect($result)->toBeInstanceOf(KpiMetricType::class)
        ->and($result->getId())->toBe(1);
});

test('updateMetricType throws exception when ranges overlap', function (): void {
    // Arrange
    $updateRequest = new UpdateKpiMetricTypeRequest(
        id: 1,
        name: null,
        planType: PaymentPlanType::RANGES,
        ranges: [
            new UpdateKpiRangeRequest(
                startPercent: 0,
                endPercent: 60,
                kpiPercent: 25
            ),
            new UpdateKpiRangeRequest(
                startPercent: 50,
                endPercent: 100,
                kpiPercent: 75
            ),
        ],
        isActive: null
    );

    $existingMetricType = new KpiMetricType(
        id: 1,
        name: 'Original Metric',
        planType: PaymentPlanType::LINEAR,
        ranges: [],
        isActive: true
    );

    $updatedMetricType = new KpiMetricType(
        id: 1,
        name: 'Original Metric',
        planType: PaymentPlanType::RANGES,
        ranges: [],
        isActive: true
    );

    // Expect transaction to be started
    $this->transaction->shouldReceive('beginTransaction')->once();

    // Expect repository to find and update metric type
    $this->readMetricTypeRepo->shouldReceive('findOrFail')
        ->once()
        ->with(1, 'Не найден тип метрики')
        ->andReturn($existingMetricType);

    $this->writeMetricTypeRepo->shouldReceive('updateMetricType')
        ->once()
        ->andReturn($updatedMetricType);

    // Expect ranges to be deleted
    $this->writeRangesRepo->shouldReceive('deleteRangesByMetricTypeId')
        ->once()
        ->with(1);

    $this->writeRangesRepo->shouldReceive('addRange')
        ->once()
    ;
    // Expect range validation to fail for the second range
    $this->readRangesRepo->shouldReceive('existInRange')
        ->once()
        ->andReturn(false);
    $this->readRangesRepo->shouldReceive('existInRange')
        ->once()
        ->andReturn(true);

    // Act & Assert
    expect(fn () => $this->useCase->updateMetricType($updateRequest))
        ->toThrow(ConflictHttpException::class, 'Диапазон пересекается с уже существующим диапазоном');
});
