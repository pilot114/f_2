<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\DepartmentResponse;
use App\Domain\Finance\Kpi\DTO\EmployeeSearchKpiResponse;
use App\Domain\Finance\Kpi\DTO\EmployeeSearchResponse;
use App\Domain\Finance\Kpi\DTO\PositionResponse;
use App\Domain\Finance\Kpi\DTO\SearchEmployeeRequest;
use App\Domain\Finance\Kpi\Repository\KpiEmployeeSearchQueryRepository;
use App\Domain\Finance\Kpi\UseCase\SearchEmployeeUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->employeeSearchRepo = Mockery::mock(KpiEmployeeSearchQueryRepository::class);
    $this->securityRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->useCase = new SearchEmployeeUseCase($this->employeeSearchRepo, $this->securityRepo);
});

afterEach(function (): void {
    Mockery::close();
});

it('имеет зависимости от KpiEmployeeSearchQueryRepository и SecurityQueryRepository', function (): void {
    $reflection = new ReflectionClass(SearchEmployeeUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('employeeSearchRepo')
        ->and($parameters[0]->getType()?->getName())->toBe('App\Domain\Finance\Kpi\Repository\KpiEmployeeSearchQueryRepository')
        ->and($parameters[1]->getName())->toBe('securityRepo')
        ->and($parameters[1]->getType()?->getName())->toBe('App\Domain\Portal\Security\Repository\SecurityQueryRepository');
});

it('имеет метод searchEmployee', function (): void {
    $reflection = new ReflectionClass(SearchEmployeeUseCase::class);

    expect($reflection->hasMethod('searchEmployee'))->toBeTrue();

    $method = $reflection->getMethod('searchEmployee');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('вызывает searchEmployeeForAdmin когда пользователь является админом', function (): void {
    $currentUserId = 123;
    $request = new SearchEmployeeRequest(search: 'Иванов');

    $expectedResponse = [
        new EmployeeSearchResponse(
            id: 1,
            name: 'Иванов Иван Иванович',
            userpic: 'https://example.com/userpic1.jpg',
            isActive: true,
            isTechnical: false,
            hasUuId: true,
            kpi: new EmployeeSearchKpiResponse(
                hasNoKpi: false,
                hasKpi: true,
                hasTwoMonthKpi: false,
                hasFourMonthKpi: false,
                kpiLastChangeDate: new DateTimeImmutable('2024-01-01'),
                hasSalaryUU: true,
            ),
            position: new PositionResponse(
                id: 1,
                name: 'Менеджер',
                isMain: true,
                fromDate: new DateTimeImmutable('2024-01-01'),
            ),
            department: new DepartmentResponse(
                name: 'Отдел продаж',
                hasKpi: true,
                bossName: 'Петров Петр Петрович',
                bossUserpic: 'https://example.com/userpic2.jpg',
                bossPositionName: 'Директор по продажам',
            ),
        ),
    ];

    $this->securityRepo
        ->expects('hasCpAction')
        ->with($currentUserId, 'accured_kpi.accured_kpi_admin')
        ->andReturns(true);

    $this->employeeSearchRepo
        ->expects('searchEmployeeForAdmin')
        ->with('Иванов')
        ->andReturns($expectedResponse);

    $result = $this->useCase->searchEmployee($currentUserId, $request);

    expect($result)->toBe($expectedResponse)
        ->and($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(EmployeeSearchResponse::class);
});

it('вызывает searchEmployeeForDepartmentBoss когда пользователь не является админом', function (): void {
    $currentUserId = 123;
    $request = new SearchEmployeeRequest(search: 'Петров');

    $expectedResponse = [
        new EmployeeSearchResponse(
            id: 2,
            name: 'Петров Петр Петрович',
            userpic: 'https://example.com/userpic3.jpg',
            isActive: true,
            isTechnical: false,
            hasUuId: true,
            kpi: new EmployeeSearchKpiResponse(
                hasNoKpi: false,
                hasKpi: true,
                hasTwoMonthKpi: true,
                hasFourMonthKpi: false,
                kpiLastChangeDate: new DateTimeImmutable('2024-02-01'),
                hasSalaryUU: true,
            ),
            position: new PositionResponse(
                id: 2,
                name: 'Руководитель',
                isMain: true,
                fromDate: new DateTimeImmutable('2024-01-01'),
            ),
            department: new DepartmentResponse(
                name: 'Отдел маркетинга',
                hasKpi: true,
                bossName: 'Сидоров Сидор Сидорович',
                bossUserpic: 'https://example.com/userpic4.jpg',
                bossPositionName: 'Директор по маркетингу',
            ),
        ),
    ];

    $this->securityRepo
        ->expects('hasCpAction')
        ->with($currentUserId, 'accured_kpi.accured_kpi_admin')
        ->andReturns(false);

    $this->employeeSearchRepo
        ->expects('searchEmployeeForDepartmentBoss')
        ->with($currentUserId, 'Петров')
        ->andReturns($expectedResponse);

    $result = $this->useCase->searchEmployee($currentUserId, $request);

    expect($result)->toBe($expectedResponse)
        ->and($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(EmployeeSearchResponse::class);
});

it('использует userId из запроса когда он указан и пользователь не админ', function (): void {
    $currentUserId = 123;
    $requestUserId = 456;
    $request = new SearchEmployeeRequest(search: 'Сидоров', userId: $requestUserId);

    $this->securityRepo
        ->expects('hasCpAction')
        ->with($currentUserId, 'accured_kpi.accured_kpi_admin')
        ->andReturns(false);

    $this->employeeSearchRepo
        ->expects('searchEmployeeForDepartmentBoss')
        ->with($requestUserId, 'Сидоров')
        ->andReturns([]);

    $result = $this->useCase->searchEmployee($currentUserId, $request);

    expect($result)->toBeArray();
});

it('использует currentUserId когда userId не указан в запросе и пользователь не админ', function (): void {
    $currentUserId = 789;
    $request = new SearchEmployeeRequest(search: 'Александров');

    $this->securityRepo
        ->expects('hasCpAction')
        ->with($currentUserId, 'accured_kpi.accured_kpi_admin')
        ->andReturns(false);

    $this->employeeSearchRepo
        ->expects('searchEmployeeForDepartmentBoss')
        ->with($currentUserId, 'Александров')
        ->andReturns([]);

    $result = $this->useCase->searchEmployee($currentUserId, $request);

    expect($result)->toBeArray();
});

it('возвращает пустой массив когда сотрудники не найдены', function (): void {
    $currentUserId = 123;
    $request = new SearchEmployeeRequest(search: 'НесуществующийСотрудник');

    $this->securityRepo
        ->expects('hasCpAction')
        ->with($currentUserId, 'accured_kpi.accured_kpi_admin')
        ->andReturns(true);

    $this->employeeSearchRepo
        ->expects('searchEmployeeForAdmin')
        ->with('НесуществующийСотрудник')
        ->andReturns([]);

    $result = $this->useCase->searchEmployee($currentUserId, $request);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});
