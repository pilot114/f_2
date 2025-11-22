<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParameters as DdmrpParametersDto;
use App\Domain\OperationalEfficiency\DDMRP\DTO\SetDdmrpParametersRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\DdmrpParametersCommandRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\SetDdmrpParametersUseCase;
use Database\ORM\Attribute\Loader;
use Mockery;

beforeEach(function (): void {
    $this->read = Mockery::mock(CokQueryRepository::class);
    $this->write = Mockery::mock(DdmrpParametersCommandRepository::class);

    $this->useCase = new SetDdmrpParametersUseCase(
        $this->read,
        $this->write
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('creates new ddmrp parameters when record does not exist', function (): void {
    $contract = 'TEST-001';

    $parametersDto = new DdmrpParametersDto(
        dvf: 1.5,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    $request = new SetDdmrpParametersRequest(
        ddmrpParameters: $parametersDto,
        contract: $contract
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

    $this->read->shouldReceive('getCokByContract')
        ->once()
        ->with($contract)
        ->andReturn($cok);

    $this->write->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($param) use ($parametersDto): bool {
            return $param instanceof DdmrpParameters
                && $param->getDvf() === $parametersDto->dvf
                && $param->getDltf() === $parametersDto->dltf
                && $param->getDlt() === $parametersDto->dlt
                && $param->getReOrderPoint() === $parametersDto->reOrderPoint
                && $param->getExpirationPercent() === $parametersDto->expirationPercent
                && $param->getMoq() === $parametersDto->moq
                && $param->getSlt() === $parametersDto->slt;
        }));

    $result = $this->useCase->setParameters($request);

    expect($result)->toBeTrue();
});

it('updates existing ddmrp parameters when record exists', function (): void {
    $contract = 'TEST-002';

    $parametersDto = new DdmrpParametersDto(
        dvf: 2.0,
        dltf: 15,
        dlt: 7,
        reOrderPoint: 150,
        expirationPercent: 75,
        moq: 60,
        slt: 90
    );

    $request = new SetDdmrpParametersRequest(
        ddmrpParameters: $parametersDto,
        contract: $contract
    );

    $ddmrpParameters = new DdmrpParameters(
        id: 100,
        contract: $contract,
        dvf: 1.0,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
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
        ->with(Mockery::on(function ($param) use ($parametersDto): bool {
            return $param instanceof DdmrpParameters
                && $param->getDvf() === $parametersDto->dvf
                && $param->getDltf() === $parametersDto->dltf
                && $param->getDlt() === $parametersDto->dlt
                && $param->getReOrderPoint() === $parametersDto->reOrderPoint
                && $param->getExpirationPercent() === $parametersDto->expirationPercent
                && $param->getMoq() === $parametersDto->moq
                && $param->getSlt() === $parametersDto->slt;
        }));

    $result = $this->useCase->setParameters($request);

    expect($result)->toBeTrue();
});
