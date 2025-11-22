<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Dit\Reporter\UseCase;

use App\Domain\Dit\Reporter\Entity\ReportQuery;
use App\Domain\Dit\Reporter\Service\ReporterExcel;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Dit\Reporter\UseCase\ExportReportUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(ExportReportUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(3)
        ->and($parameters[0]->getName())->toBe('reporterExcel')
        ->and($parameters[1]->getName())->toBe('executeReportUseCase')
        ->and($parameters[2]->getName())->toBe('access');
});

it('has export method with correct signature', function (): void {
    $reflection = new ReflectionClass(ExportReportUseCase::class);
    $method = $reflection->getMethod('export');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(3)
        ->and($parameters[0]->getName())->toBe('reportId')
        ->and($parameters[1]->getName())->toBe('input')
        ->and($parameters[2]->getName())->toBe('currentUser');
});

it('has correct return type for export method', function (): void {
    $reflection = new ReflectionClass(ExportReportUseCase::class);
    $method = $reflection->getMethod('export');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('Symfony\Component\HttpFoundation\File\UploadedFile');
});

it('export method parameters have correct types', function (): void {
    $reflection = new ReflectionClass(ExportReportUseCase::class);
    $method = $reflection->getMethod('export');

    $parameters = $method->getParameters();
    $reportIdParam = $parameters[0];
    $inputParam = $parameters[1];
    $currentUserParam = $parameters[2];

    expect($reportIdParam->getType()?->getName())->toBe('int')
        ->and($inputParam->getType()?->getName())->toBe('array')
        ->and($currentUserParam->getType()?->getName())->toBe('App\Domain\Portal\Security\Entity\SecurityUser');
});

it('has protected properties', function (): void {
    $reflection = new ReflectionClass(ExportReportUseCase::class);

    expect($reflection->hasProperty('reporterExcel'))->toBeTrue()
        ->and($reflection->hasProperty('executeReportUseCase'))->toBeTrue();

    $reporterExcel = $reflection->getProperty('reporterExcel');
    $executeReportUseCase = $reflection->getProperty('executeReportUseCase');

    expect($reporterExcel->isProtected())->toBeTrue()
        ->and($executeReportUseCase->isProtected())->toBeTrue();
});

it('exports report when user has permission', function (): void {
    $reportId = 123;
    $input = [
        'param1' => 'value1',
    ];
    $currentUser = createSecurityUser(id: 42);

    $reporterExcel = Mockery::mock(ReporterExcel::class);
    $executeReportUseCase = Mockery::mock(ExecuteReportUseCase::class);
    $access = Mockery::mock(SecurityQueryRepository::class);

    $useCase = new ExportReportUseCase($reporterExcel, $executeReportUseCase, $access);

    $access->shouldReceive('hasPermission')
        ->once()
        ->with(42, 'rep_report', $reportId)
        ->andReturn(true);

    $reportData = [
        [
            'col1' => 'val1',
            'col2' => 'val2',
        ],
        [
            'col1' => 'val3',
            'col2' => 'val4',
        ],
    ];

    $reportQuery = new ReportQuery(sql: 'SELECT * FROM test', fields: ['col1', 'col2']);
    $executeReportUseCase->reportQuery = $reportQuery;

    $executeReportUseCase->shouldReceive('executeReport')
        ->once()
        ->with($reportId, $currentUser, $input, true)
        ->andReturn([$reportData, 2]);

    $uploadedFile = Mockery::mock(UploadedFile::class);

    $reporterExcel->shouldReceive('setName')
        ->once()
        ->with("Отчёт $reportId")
        ->andReturnSelf();

    $reporterExcel->shouldReceive('setContent')
        ->once()
        ->with($reportData, Mockery::any())
        ->andReturnSelf();

    $reporterExcel->shouldReceive('getFile')
        ->once()
        ->andReturn($uploadedFile);

    $result = $useCase->export($reportId, $input, $currentUser);

    expect($result)->toBe($uploadedFile);

    Mockery::close();
});

it('throws exception when user has no permission', function (): void {
    $reportId = 456;
    $input = [];
    $currentUser = createSecurityUser(id: 99, name: 'Unauthorized User', email: 'unauth@example.com');

    $reporterExcel = Mockery::mock(ReporterExcel::class);
    $executeReportUseCase = Mockery::mock(ExecuteReportUseCase::class);
    $access = Mockery::mock(SecurityQueryRepository::class);

    $useCase = new ExportReportUseCase($reporterExcel, $executeReportUseCase, $access);

    $access->shouldReceive('hasPermission')
        ->once()
        ->with(99, 'rep_report', $reportId)
        ->andReturn(false);

    $executeReportUseCase->shouldNotReceive('executeReport');
    $reporterExcel->shouldNotReceive('setName');

    expect(fn (): UploadedFile => $useCase->export($reportId, $input, $currentUser))
        ->toThrow(AccessDeniedHttpException::class, "Нет прав на отчёт $reportId");

    Mockery::close();
});

it('uses allData parameter when executing report', function (): void {
    $reportId = 789;
    $input = [
        'filter' => 'test',
    ];
    $currentUser = createSecurityUser(id: 1, name: 'User', email: 'user@example.com');

    $reporterExcel = Mockery::mock(ReporterExcel::class);
    $executeReportUseCase = Mockery::mock(ExecuteReportUseCase::class);
    $access = Mockery::mock(SecurityQueryRepository::class);

    $useCase = new ExportReportUseCase($reporterExcel, $executeReportUseCase, $access);

    $access->shouldReceive('hasPermission')->andReturn(true);

    // Verify allData is set to true
    $executeReportUseCase->shouldReceive('executeReport')
        ->once()
        ->withArgs(function ($id, $user, $params, $allData) use ($reportId, $currentUser, $input): bool {
            return $id === $reportId
                && $user === $currentUser
                && $params === $input
                && $allData === true;
        })
        ->andReturn([[], 0]);

    $reportQuery = new ReportQuery(sql: 'SELECT * FROM data');
    $executeReportUseCase->reportQuery = $reportQuery;

    $uploadedFile = Mockery::mock(UploadedFile::class);
    $reporterExcel->shouldReceive('setName')->andReturnSelf();
    $reporterExcel->shouldReceive('setContent')->andReturnSelf();
    $reporterExcel->shouldReceive('getFile')->andReturn($uploadedFile);

    $result = $useCase->export($reportId, $input, $currentUser);

    expect($result)->toBe($uploadedFile);

    Mockery::close();
});
