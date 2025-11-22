<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokCommandRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\ChangeCokCalculationStatusUseCase;
use Database\ORM\Attribute\Loader;
use Mockery;

beforeEach(function (): void {
    $this->read = Mockery::mock(CokQueryRepository::class);
    $this->write = Mockery::mock(CokCommandRepository::class);

    $this->useCase = new ChangeCokCalculationStatusUseCase(
        $this->write,
        $this->read
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('changes calculation status to ADDED', function (): void {
    $contract = 'TEST-001';
    $newStatus = CalculationStatus::ADDED;

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
        calculationStatus: CalculationStatus::NOT_VERIFIED,
        address: null,
        phone: null,
        email: null,
        ddmrpParameters: $ddmrpParameters
    );

    $this->read->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->write->shouldReceive('update')
        ->once()
        ->with($cok);

    $result = $this->useCase->changeStatus($newStatus, $contract);

    expect($result)->toBeTrue()
        ->and($cok->getCalculationStatus())->toBe($newStatus);
});

it('changes calculation status to EXCLUDED', function (): void {
    $contract = 'TEST-002';
    $newStatus = CalculationStatus::EXCLUDED;

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

    $this->read->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->write->shouldReceive('update')
        ->once()
        ->with($cok);

    $result = $this->useCase->changeStatus($newStatus, $contract);

    expect($result)->toBeTrue()
        ->and($cok->getCalculationStatus())->toBe($newStatus);
});
