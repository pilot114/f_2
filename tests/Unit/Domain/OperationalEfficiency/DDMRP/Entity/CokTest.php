<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParameters as DdmrpParametersDto;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;
use App\Domain\OperationalEfficiency\DDMRP\Entity\GrandManager;
use App\Domain\OperationalEfficiency\DDMRP\Entity\RegionalDirector;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use Database\ORM\Attribute\Loader;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('creates cok with basic fields', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-001',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-001',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: '123 Test St',
        phone: '+1234567890',
        email: 'test@cok.com',
        ddmrpParameters: $ddmrpParameters
    );

    expect($cok->departmentId)->toBe(100);
});

it('returns NOT_VERIFIED as default calculation status', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-002',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-002',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: null,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect($cok->getCalculationStatus())->toBe(CalculationStatus::NOT_VERIFIED);
});

it('changes calculation status to ADDED', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-003',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-003',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::NOT_VERIFIED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $cok->changeCalculationStatus(CalculationStatus::ADDED);

    expect($cok->getCalculationStatus())->toBe(CalculationStatus::ADDED);
});

it('changes calculation status to EXCLUDED', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-004',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-004',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $cok->changeCalculationStatus(CalculationStatus::EXCLUDED);

    expect($cok->getCalculationStatus())->toBe(CalculationStatus::EXCLUDED);
});

it('throws exception when changing status to NOT_VERIFIED', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-005',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-005',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect(fn () => $cok->changeCalculationStatus(CalculationStatus::NOT_VERIFIED))
        ->toThrow(InvariantDomainException::class, 'Нельзя перевести ЦОК в "Непроверенные"');
});

it('allows changing ddmrp parameters when not excluded', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: 100,
        contract: 'TEST-006',
        dvf: 1.0,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-006',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect($cok->canChangeDdmrpParameters())->toBeTrue();
});

it('throws exception when changing ddmrp parameters for excluded cok', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: 100,
        contract: 'TEST-007',
        dvf: 1.0,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-007',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::EXCLUDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect(fn (): bool => $cok->canChangeDdmrpParameters())
        ->toThrow(InvariantDomainException::class, 'Нельзя менять параметры у ЦОКов в статусе "Исключенные"');
});

it('updates ddmrp parameters', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: 100,
        contract: 'TEST-008',
        dvf: 1.0,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-008',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $newParametersDto = new DdmrpParametersDto(
        dvf: 2.0,
        dltf: 15,
        dlt: 7,
        reOrderPoint: 150,
        expirationPercent: 75,
        moq: 60,
        slt: 90
    );

    $cok->updateDdmrpParameters($newParametersDto);

    $updatedParams = $cok->getDdmrpParameters();
    expect($updatedParams->getDvf())->toBe(2.0)
        ->and($updatedParams->getDltf())->toBe(15)
        ->and($updatedParams->getDlt())->toBe(7)
        ->and($updatedParams->getReOrderPoint())->toBe(150)
        ->and($updatedParams->getExpirationPercent())->toBe(75)
        ->and($updatedParams->getMoq())->toBe(60)
        ->and($updatedParams->getSlt())->toBe(90);
});

it('allows changing employee access when not excluded', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-009',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-009',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect($cok->canChangeEmployeeAccess())->toBeTrue();
});

it('throws exception when changing employee access for excluded cok', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-010',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-010',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::EXCLUDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    expect(fn (): bool => $cok->canChangeEmployeeAccess())
        ->toThrow(InvariantDomainException::class, 'Нельзя менять доступ сотрудников для ЦОКов в статусе "Исключенные"');
});

it('adds employees to cok', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-011',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-011',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $employee1 = new CokEmployee(
        id: 1,
        cokContract: 'TEST-011',
        departmentId: 100,
        name: 'Employee 1',
        response: null,
        email: 'emp1@test.com',
        phone: null,
        accessToDdmrp: null
    );

    $employee2 = new CokEmployee(
        id: 2,
        cokContract: 'TEST-011',
        departmentId: 100,
        name: 'Employee 2',
        response: null,
        email: 'emp2@test.com',
        phone: null,
        accessToDdmrp: null
    );

    $cok->addEmployees([$employee1, $employee2]);

    expect($cok->getEmployee(1))->toBe($employee1)
        ->and($cok->getEmployee(2))->toBe($employee2);
});

it('gets employee by id', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-012',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-012',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $employee = new CokEmployee(
        id: 999,
        cokContract: 'TEST-012',
        departmentId: 100,
        name: 'Test Employee',
        response: null,
        email: 'test@test.com',
        phone: null,
        accessToDdmrp: null
    );

    $cok->addEmployees([$employee]);

    expect($cok->getEmployee(999))->toBe($employee);
});

it('throws exception when employee not found', function (): void {
    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: 'TEST-013',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-013',
        name: 'Test COK',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $cok->addEmployees([]);

    expect(fn (): CokEmployee => $cok->getEmployee(999))
        ->toThrow(NotFoundHttpException::class, 'сотрудника с id = 999 нет в Цоке TEST-013');
});

it('converts to CokResponse with all fields', function (): void {
    $regionalDirector = new RegionalDirector(
        id: 10,
        name: 'John Regional'
    );

    $grandManager = new GrandManager(
        id: 20,
        name: 'Jane Manager'
    );

    $ddmrpParameters = new DdmrpParameters(
        id: 100,
        contract: 'TEST-014',
        dvf: 1.5,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    $cok = new Cok(
        id: 1,
        departmentId: 100,
        contract: 'TEST-014',
        name: 'Test COK Full',
        regionalDirector: $regionalDirector,
        grandManager: $grandManager,
        calculationStatus: CalculationStatus::ADDED,
        address: '123 Main St',
        phone: '+1234567890',
        email: 'test@cok.com',
        ddmrpParameters: $ddmrpParameters
    );

    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-014',
        departmentId: 100,
        name: 'Employee 1',
        response: null,
        email: 'emp1@test.com',
        phone: null,
        accessToDdmrp: null
    );

    $cok->addEmployees([$employee]);

    $response = $cok->toCokResponse();

    expect($response->id)->toBe(1)
        ->and($response->departmentId)->toBe(100)
        ->and($response->contract)->toBe('TEST-014')
        ->and($response->name)->toBe('Test COK Full')
        ->and($response->regionalDirector)->not->toBeNull()
        ->and($response->regionalDirector->id)->toBe(10)
        ->and($response->grandManager)->not->toBeNull()
        ->and($response->grandManager->id)->toBe(20)
        ->and($response->calculationStatus->id)->toBe(CalculationStatus::ADDED->value)
        ->and($response->address)->toBe('123 Main St')
        ->and($response->phone)->toBe('+1234567890')
        ->and($response->email)->toBe('test@cok.com')
        ->and($response->ddmrpParameters->dvf)->toBe(1.5)
        ->and($response->employees)->toHaveCount(1);
});
