<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParameters as DdmrpParametersDto;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpParameters;

it('creates ddmrp parameters with all fields', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-001',
        dvf: 1.5,
        dltf: 10,
        dlt: 5,
        reOrderPoint: 100,
        expirationPercent: 80,
        moq: 50,
        slt: 95
    );

    expect($parameters->getId())->toBe(1)
        ->and($parameters->getContract())->toBe('TEST-001')
        ->and($parameters->getDvf())->toBe(1.5)
        ->and($parameters->getDltf())->toBe(10)
        ->and($parameters->getDlt())->toBe(5)
        ->and($parameters->getReOrderPoint())->toBe(100)
        ->and($parameters->getExpirationPercent())->toBe(80)
        ->and($parameters->getMoq())->toBe(50)
        ->and($parameters->getSlt())->toBe(95);
});

it('creates ddmrp parameters with null values', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-002',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    expect($parameters->getId())->toBe(1)
        ->and($parameters->getContract())->toBe('TEST-002')
        ->and($parameters->getDvf())->toBeNull()
        ->and($parameters->getDltf())->toBeNull()
        ->and($parameters->getDlt())->toBeNull()
        ->and($parameters->getReOrderPoint())->toBeNull()
        ->and($parameters->getExpirationPercent())->toBeNull()
        ->and($parameters->getMoq())->toBeNull()
        ->and($parameters->getSlt())->toBeNull();
});

it('returns true for isRecordExists when id is positive', function (): void {
    $parameters = new DdmrpParameters(
        id: 100,
        contract: 'TEST-003',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    expect($parameters->isRecordExists())->toBeTrue();
});

it('returns false for isRecordExists when id is zero or negative', function (): void {
    $parameters = new DdmrpParameters(
        id: 0,
        contract: 'TEST-004',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    expect($parameters->isRecordExists())->toBeFalse();
});

it('updates all parameters from dto', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-005',
        dvf: 1.0,
        dltf: 5,
        dlt: 3,
        reOrderPoint: 50,
        expirationPercent: 70,
        moq: 25,
        slt: 85
    );

    $dto = new DdmrpParametersDto(
        dvf: 2.5,
        dltf: 15,
        dlt: 7,
        reOrderPoint: 150,
        expirationPercent: 90,
        moq: 75,
        slt: 98
    );

    $parameters->update($dto);

    expect($parameters->getDvf())->toBe(2.5)
        ->and($parameters->getDltf())->toBe(15)
        ->and($parameters->getDlt())->toBe(7)
        ->and($parameters->getReOrderPoint())->toBe(150)
        ->and($parameters->getExpirationPercent())->toBe(90)
        ->and($parameters->getMoq())->toBe(75)
        ->and($parameters->getSlt())->toBe(98);
});

it('updates parameters with null values from dto', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-006',
        dvf: 1.0,
        dltf: 5,
        dlt: 3,
        reOrderPoint: 50,
        expirationPercent: 70,
        moq: 25,
        slt: 85
    );

    $dto = new DdmrpParametersDto(
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $parameters->update($dto);

    expect($parameters->getDvf())->toBeNull()
        ->and($parameters->getDltf())->toBeNull()
        ->and($parameters->getDlt())->toBeNull()
        ->and($parameters->getReOrderPoint())->toBeNull()
        ->and($parameters->getExpirationPercent())->toBeNull()
        ->and($parameters->getMoq())->toBeNull()
        ->and($parameters->getSlt())->toBeNull();
});

it('converts to DdmrpParametersResponse', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-007',
        dvf: 1.8,
        dltf: 12,
        dlt: 6,
        reOrderPoint: 120,
        expirationPercent: 85,
        moq: 60,
        slt: 92
    );

    $response = $parameters->toDdmrpParametersResponse();

    expect($response->dvf)->toBe(1.8)
        ->and($response->dltf)->toBe(12)
        ->and($response->dlt)->toBe(6)
        ->and($response->reOrderPoint)->toBe(120)
        ->and($response->expirationPercent)->toBe(85)
        ->and($response->moq)->toBe(60)
        ->and($response->slt)->toBe(92);
});

it('converts to DdmrpParametersResponse with null values', function (): void {
    $parameters = new DdmrpParameters(
        id: 1,
        contract: 'TEST-008',
        dvf: null,
        dltf: null,
        dlt: null,
        reOrderPoint: null,
        expirationPercent: null,
        moq: null,
        slt: null
    );

    $response = $parameters->toDdmrpParametersResponse();

    expect($response->dvf)->toBeNull()
        ->and($response->dltf)->toBeNull()
        ->and($response->dlt)->toBeNull()
        ->and($response->reOrderPoint)->toBeNull()
        ->and($response->expirationPercent)->toBeNull()
        ->and($response->moq)->toBeNull()
        ->and($response->slt)->toBeNull();
});
