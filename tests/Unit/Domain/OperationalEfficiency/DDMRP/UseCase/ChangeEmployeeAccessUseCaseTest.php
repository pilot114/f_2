<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\ChangeEmployeeAccessRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpEmployeeAccess;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\DdmrpEmployeeAccessCommandRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\ChangeEmployeeAccessUseCase;
use App\Domain\Portal\Security\Enum\AccessType;
use App\Domain\Portal\Security\Repository\SecurityCommandRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Mockery;

beforeEach(function (): void {
    $this->cokRead = Mockery::mock(CokQueryRepository::class);
    $this->accessWrite = Mockery::mock(DdmrpEmployeeAccessCommandRepository::class);
    $this->cpMenuAccessWrite = Mockery::mock(SecurityCommandRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new ChangeEmployeeAccessUseCase(
        $this->cokRead,
        $this->accessWrite,
        $this->cpMenuAccessWrite,
        $this->transaction
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('grants access to employee without existing access', function (): void {
    $contract = 'TEST-001';
    $employeeId = 999;

    $request = new ChangeEmployeeAccessRequest(
        contract: $contract,
        employeeId: $employeeId,
        grantAccess: true
    );

    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: $contract,
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
        contract: $contract,
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
        id: $employeeId,
        cokContract: $contract,
        departmentId: 100,
        name: 'Test Employee',
        response: null,
        email: 'test@example.com',
        phone: null,
        accessToDdmrp: null
    );

    $cok->addEmployees([$employee]);

    $this->cokRead->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->accessWrite->shouldReceive('create')
        ->once()
        ->with(Mockery::type(DdmrpEmployeeAccess::class));
    foreach ([AccessType::READ, AccessType::WRITE] as $case) {
        $this->cpMenuAccessWrite->shouldReceive('grantCpMenuAccess')
            ->once()
            ->with($employeeId, ChangeEmployeeAccessUseCase::DDMRP_ORDER_CP_MENU_ID, $case);
    }

    $this->transaction->shouldReceive('commit')->once();

    $result = $this->useCase->changeAccess($request);

    expect($result)->toBeTrue();
});

it('grants access to employee with existing access - does nothing', function (): void {
    $contract = 'TEST-002';
    $employeeId = 888;

    $request = new ChangeEmployeeAccessRequest(
        contract: $contract,
        employeeId: $employeeId,
        grantAccess: true
    );

    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: $contract,
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 2,
        departmentId: 200,
        contract: $contract,
        name: 'Test COK 2',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $existingAccess = new DdmrpEmployeeAccess(
        id: 50,
        contract: $contract,
        cpEmpId: $employeeId
    );

    $employee = new CokEmployee(
        id: $employeeId,
        cokContract: $contract,
        departmentId: 200,
        name: 'Test Employee 2',
        response: null,
        email: 'test2@example.com',
        phone: null,
        accessToDdmrp: $existingAccess
    );

    $cok->addEmployees([$employee]);

    $this->cokRead->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->accessWrite->shouldNotReceive('create');

    $result = $this->useCase->changeAccess($request);

    expect($result)->toBeTrue();
});

it('revokes access from employee with existing access', function (): void {
    $contract = 'TEST-003';
    $employeeId = 777;

    $request = new ChangeEmployeeAccessRequest(
        contract: $contract,
        employeeId: $employeeId,
        grantAccess: false
    );

    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: $contract,
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 3,
        departmentId: 300,
        contract: $contract,
        name: 'Test COK 3',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $existingAccess = new DdmrpEmployeeAccess(
        id: 60,
        contract: $contract,
        cpEmpId: $employeeId
    );

    $employee = new CokEmployee(
        id: $employeeId,
        cokContract: $contract,
        departmentId: 300,
        name: 'Test Employee 3',
        response: null,
        email: 'test3@example.com',
        phone: null,
        accessToDdmrp: $existingAccess
    );

    $cok->addEmployees([$employee]);

    $this->cokRead->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->accessWrite->shouldReceive('delete')
        ->once()
        ->with(60);

    $result = $this->useCase->changeAccess($request);

    expect($result)->toBeTrue();
});

it('revokes access from employee without existing access - does nothing', function (): void {
    $contract = 'TEST-004';
    $employeeId = 666;

    $request = new ChangeEmployeeAccessRequest(
        contract: $contract,
        employeeId: $employeeId,
        grantAccess: false
    );

    $ddmrpParameters = new DdmrpParameters(
        id: Loader::ID_FOR_INSERT,
        contract: $contract,
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $cok = new Cok(
        id: 4,
        departmentId: 400,
        contract: $contract,
        name: 'Test COK 4',
        regionalDirector: null,
        grandManager: null,
        calculationStatus: CalculationStatus::ADDED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $employee = new CokEmployee(
        id: $employeeId,
        cokContract: $contract,
        departmentId: 400,
        name: 'Test Employee 4',
        response: null,
        email: 'test4@example.com',
        phone: null,
        accessToDdmrp: null
    );

    $cok->addEmployees([$employee]);

    $this->cokRead->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->accessWrite->shouldNotReceive('delete');

    $result = $this->useCase->changeAccess($request);

    expect($result)->toBeTrue();
});
