<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\DTO\CreateKpiMetricDepartmentRequest;
use App\Domain\Finance\Kpi\DTO\CreateKpiMetricRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricRequest;
use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Entity\Post;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use App\Domain\Finance\Kpi\Repository\KpiMetricCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\WriteMetricUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->writeKpiMetricRepo = Mockery::mock(KpiMetricCommandRepository::class);
    $this->readKpiMetricRepo = Mockery::mock(KpiMetricQueryRepository::class);
    $this->readMetricGroup = Mockery::mock(QueryRepositoryInterface::class);
    $this->readMetricType = Mockery::mock(QueryRepositoryInterface::class);
    $this->readPost = Mockery::mock(QueryRepositoryInterface::class);
    $this->readDepartment = Mockery::mock(QueryRepositoryInterface::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new WriteMetricUseCase(
        $this->writeKpiMetricRepo,
        $this->readKpiMetricRepo,
        $this->readMetricGroup,
        $this->readMetricType,
        $this->readPost,
        $this->readDepartment,
        $this->transaction
    );
});

it('creates a new metric successfully', function (): void {
    // Arrange
    $groupId = 1;
    $metricTypeId = 2;
    $departmentId = 3;
    $postId = 4;

    $group = Mockery::mock(KpiMetricGroup::class);
    $type = Mockery::mock(KpiMetricType::class);
    $department = Mockery::mock(CpDepartment::class);
    $post = Mockery::mock(Post::class);

    // Setup expectations for repositories
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->readMetricGroup->shouldReceive('findOrFail')
        ->with($groupId, 'Не найдена группа метрик')
        ->andReturn($group);

    $this->readMetricType->shouldReceive('findOrFail')
        ->with($metricTypeId, 'Не найден тип метрики')
        ->andReturn($type);

    // For department link
    $this->readDepartment->shouldReceive('findOrFail')
        ->with($departmentId, 'Не найден департамент')
        ->andReturn($department);

    $this->readPost->shouldReceive('findOrFail')
        ->with($postId, 'Не найдена должность')
        ->andReturn($post);

    // Create mock for the return value
    $metric = Mockery::mock(KpiMetric::class);

    // The repository will create the metric
    $this->writeKpiMetricRepo->shouldReceive('createMetric')
        ->once()
        ->andReturn($metric);

    // Link department to metric
    $this->writeKpiMetricRepo->shouldReceive('createLinkToDepartmentForMetric')
        ->once()
        ->with($metric, $department, $post);

    // Create the request
    $request = new CreateKpiMetricRequest(
        name: 'Test Metric',
        kpiType: KpiType::MONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'Auto calculation description',
        unitType: UnitType::PERCENTS,
        groupId: $groupId,
        metricTypeId: $metricTypeId,
        metricDepartments: [
            new CreateKpiMetricDepartmentRequest(
                departmentId: $departmentId,
                postId: $postId
            ),
        ]
    );

    // Act
    $result = $this->useCase->createMetric($request);

    // Assert
    expect($result)->toBe($metric);
});

it('updates a metric successfully with all fields', function (): void {
    // Arrange
    $metricId = 1;
    $groupId = 2;
    $metricTypeId = 3;
    $departmentId = 4;
    $postId = 5;

    $originalMetric = Mockery::mock(KpiMetric::class);
    $group = Mockery::mock(KpiMetricGroup::class);
    $type = Mockery::mock(KpiMetricType::class);
    $department = Mockery::mock(CpDepartment::class);
    $post = Mockery::mock(Post::class);

    // Setup expectations for repositories
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->readKpiMetricRepo->shouldReceive('getMetric')
        ->with($metricId)
        ->andReturn($originalMetric);

    $this->readMetricGroup->shouldReceive('findOrFail')
        ->with($groupId, 'Не найдена группа метрик')
        ->andReturn($group);

    $this->readMetricType->shouldReceive('findOrFail')
        ->with($metricTypeId, 'Не найден тип метрики')
        ->andReturn($type);

    // Set expectations for property setters
    $originalMetric->shouldReceive('setGroup')->once()->with($group)->andReturnSelf();
    $originalMetric->shouldReceive('setType')->once()->with($type)->andReturnSelf();
    $originalMetric->shouldReceive('setName')->once()->with('Updated Name')->andReturnSelf();
    $originalMetric->shouldReceive('setKpiType')->once()->with(KpiType::QUARTERLY)->andReturnSelf();
    $originalMetric->shouldReceive('setCalculationType')->once()->with(KpiCalculationType::MANUAL)->andReturnSelf();
    $originalMetric->shouldReceive('setCalculationTypeDescription')->once()->with('Updated description')->andReturnSelf();
    $originalMetric->shouldReceive('setUnitType')->once()->with(UnitType::CONDITIONAL_UNITS)->andReturnSelf();
    $originalMetric->shouldReceive('setIsActive')->once()->with(false)->andReturnSelf();

    // For handling departments
    $originalMetric->shouldReceive('toArray')->once()->andReturn([
        'departments' => [
            [
                'departmentId' => 10,
                'postId'       => 20,
            ],
        ],
    ]);

    // Unlink existing department
    $this->writeKpiMetricRepo->shouldReceive('deleteLinkToDepartmentForMetric')
        ->once()
        ->with($originalMetric, 10, 20);

    // For adding new department link
    $this->readDepartment->shouldReceive('findOrFail')
        ->with($departmentId, 'Не найден департамент')
        ->andReturn($department);

    $this->readPost->shouldReceive('findOrFail')
        ->with($postId, 'Не найдена должность')
        ->andReturn($post);

    $this->writeKpiMetricRepo->shouldReceive('createLinkToDepartmentForMetric')
        ->once()
        ->with($originalMetric, $department, $post);

    // Update the metric
    $this->writeKpiMetricRepo->shouldReceive('updateMetric')
        ->once()
        ->with($originalMetric)
        ->andReturn($originalMetric);

    // Create the request
    $request = new UpdateKpiMetricRequest(
        id: $metricId,
        name: 'Updated Name',
        kpiType: KpiType::QUARTERLY,
        calculationType: KpiCalculationType::MANUAL,
        calculationTypeDescription: 'Updated description',
        unitType: UnitType::CONDITIONAL_UNITS,
        groupId: $groupId,
        metricTypeId: $metricTypeId,
        isActive: false,
        metricDepartments: [
            new CreateKpiMetricDepartmentRequest(
                departmentId: $departmentId,
                postId: $postId
            ),
        ]
    );

    // Act
    $result = $this->useCase->updateMetric($request);

    // Assert
    expect($result)->toBe($originalMetric);
});

it('updates a metric successfully with partial fields', function (): void {
    // Arrange
    $metricId = 1;
    $originalMetric = Mockery::mock(KpiMetric::class);

    // Setup expectations for repositories
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->readKpiMetricRepo->shouldReceive('getMetric')
        ->with($metricId)
        ->andReturn($originalMetric);

    // Set expectations for property setters - only name is updated
    $originalMetric->shouldReceive('setName')->once()->with('Updated Name')->andReturnSelf();

    // Update the metric
    $this->writeKpiMetricRepo->shouldReceive('updateMetric')
        ->once()
        ->with($originalMetric)
        ->andReturn($originalMetric);

    // No department updates in this test
    $originalMetric->shouldNotReceive('toArray');

    // Create the request with only the name field
    $request = new UpdateKpiMetricRequest(
        id: $metricId,
        name: 'Updated Name'
    );

    // Act
    $result = $this->useCase->updateMetric($request);

    // Assert
    expect($result)->toBe($originalMetric);
});

it('throws an exception when updating a non-existent metric', function (): void {
    // Arrange
    $metricId = 999; // Non-existent ID

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('rollBack')->never(); // Not implemented in the method

    $this->readKpiMetricRepo->shouldReceive('getMetric')
        ->with($metricId)
        ->andReturnNull();

    // Create the request
    $request = new UpdateKpiMetricRequest(
        id: $metricId,
        name: 'Will Not Update'
    );

    // Act & Assert
    expect(function () use ($request): void {
        $this->useCase->updateMetric($request);
    })->toThrow(NotFoundHttpException::class, "Не найдена метрика с id = {$metricId}");
});

it('can update a metric without changing departments', function (): void {
    // Arrange
    $metricId = 1;
    $originalMetric = Mockery::mock(KpiMetric::class);

    // Setup expectations for repositories
    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->readKpiMetricRepo->shouldReceive('getMetric')
        ->with($metricId)
        ->andReturn($originalMetric);

    // Set expectations for property setters
    $originalMetric->shouldReceive('setName')->once()->with('Updated Name')->andReturnSelf();

    // Update the metric
    $this->writeKpiMetricRepo->shouldReceive('updateMetric')
        ->once()
        ->with($originalMetric)
        ->andReturn($originalMetric);

    // No department updates because empty array is provided
    $originalMetric->shouldNotReceive('toArray');

    // Create the request with no department changes
    $request = new UpdateKpiMetricRequest(
        id: $metricId,
        name: 'Updated Name',
        metricDepartments: [] // Empty array, should not trigger department changes
    );

    // Act
    $result = $this->useCase->updateMetric($request);

    // Assert
    expect($result)->toBe($originalMetric);
});

afterEach(function (): void {
    Mockery::close();
});
