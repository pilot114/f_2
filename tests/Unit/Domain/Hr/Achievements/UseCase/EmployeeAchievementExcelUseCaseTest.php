<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Common\Exception\FileException;
use App\Common\Service\File\FileService;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemCommandRepository;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Hr\Achievements\UseCase\EmployeeAchievementExcelUseCase;
use Exception;
use ReflectionClass;

beforeEach(function (): void {
    $this->fileService = $this->createMock(FileService::class);
    $this->readRepository = $this->createMock(AchievementEmployeeItemQueryRepository::class);
    $this->writeRepository = $this->createMock(AchievementEmployeeItemCommandRepository::class);

    $this->useCase = new EmployeeAchievementExcelUseCase(
        $this->fileService,
        $this->readRepository,
        $this->writeRepository
    );
});

it('throws file exception when file not found', function (): void {
    $this->fileService
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    expect(fn () => $this->useCase->unlockFromExcel(999, 1))
        ->toThrow(FileException::class, 'Не найден файл с id 999!');
});

it('throws file exception when file service returns null', function (): void {
    $this->fileService
        ->expects($this->once())
        ->method('getById')
        ->with(123)
        ->willReturn(null);

    expect(fn () => $this->useCase->unlockFromExcel(123, 1))
        ->toThrow(FileException::class, 'Не найден файл с id 123!');
});

it('maps database error for invalid employee ID', function (): void {
    $reflection = new ReflectionClass($this->useCase);
    $method = $reflection->getMethod('mapDatabaseError');
    $method->setAccessible(true);

    $exception = new Exception('TEST.CP_EA_EMPLOYEE_ACHIEVMENTS_FK_CP_EMP_ID constraint failed');
    $result = $method->invoke($this->useCase, $exception);

    expect($result)->toBe('Неверный ID пользователя');
});

it('maps database error for invalid achievement ID', function (): void {
    $reflection = new ReflectionClass($this->useCase);
    $method = $reflection->getMethod('mapDatabaseError');
    $method->setAccessible(true);

    $exception = new Exception('CP_EA_EMPLOYEE_ACHIEVMENTS_FK_ACHIEVEMENT_CARDS_ID constraint failed');
    $result = $method->invoke($this->useCase, $exception);

    expect($result)->toBe('Неверный ID достижения.');
});

it('maps database error for duplicate record', function (): void {
    $reflection = new ReflectionClass($this->useCase);
    $method = $reflection->getMethod('mapDatabaseError');
    $method->setAccessible(true);

    $exception = new Exception('TEST.CP_EA_EMPLOYEE_ACHIEVMENTS_UQ_CEI_ACI_RD unique constraint violation');
    $result = $method->invoke($this->useCase, $exception);

    expect($result)->toBe('Такая запись о присвоении уже существует.');
});

it('returns original message for unmapped database error', function (): void {
    $reflection = new ReflectionClass($this->useCase);
    $method = $reflection->getMethod('mapDatabaseError');
    $method->setAccessible(true);

    $exception = new Exception('Some unknown database error');
    $result = $method->invoke($this->useCase, $exception);

    expect($result)->toBe('Some unknown database error');
});

it('maps database error case insensitive', function (): void {
    $reflection = new ReflectionClass($this->useCase);
    $method = $reflection->getMethod('mapDatabaseError');
    $method->setAccessible(true);

    $exception = new Exception('test.cp_ea_employee_achievments_fk_cp_emp_id constraint failed');
    $result = $method->invoke($this->useCase, $exception);

    expect($result)->toBe('Неверный ID пользователя');
});

// Note: Testing the full unlockFromExcel method would require:
// 1. Creating actual Excel files or mocking OpenSpout components
// 2. Mocking file system operations (tempnam, fopen, file_get_contents)
// 3. Setting up complex database interaction scenarios
//
// This would make the tests very complex and fragile. In a real-world scenario,
// this method would benefit from refactoring to separate concerns:
// - File handling logic
// - Excel parsing logic
// - Database operations
// - Data validation
//
// Each concern could then be tested independently.

it('has correct constructor dependencies', function (): void {
    expect($this->useCase)->toBeInstanceOf(EmployeeAchievementExcelUseCase::class);

    $reflection = new ReflectionClass($this->useCase);
    $constructor = $reflection->getConstructor();
    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(3);
    expect($parameters[0]->getName())->toBe('service');
    expect($parameters[1]->getName())->toBe('readRepository');
    expect($parameters[2]->getName())->toBe('writeRepository');
});
