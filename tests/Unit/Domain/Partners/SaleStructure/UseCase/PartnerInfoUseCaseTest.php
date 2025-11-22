<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Partners\SaleStructure\UseCase;

use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use App\Domain\Partners\SaleStructure\Exception\PartnerDomainException;
use App\Domain\Partners\SaleStructure\Repository\PartnerInfoRepository;
use App\Domain\Partners\SaleStructure\UseCase\PartnerInfoUseCase;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->repository = Mockery::mock(PartnerInfoRepository::class);
    $this->user = createSecurityUser(4155, 'admin', 'admin');
    $this->useCase = new PartnerInfoUseCase($this->repository, $this->user);
});

it('retrieves partner info by contract', function (): void {
    $contract = 'CONTRACT123';

    $partnerInfo = new PartnerInfo(
        1,
        'Partner Name',
        $contract,
        'Russia',
        643,
        5,
        null,
        null,
        'Rank Name'
    );

    $this->repository
        ->shouldReceive('getEmployeeByContract')
        ->with($contract)
        ->andReturn($partnerInfo);

    $this->repository
        ->shouldReceive('getEmployeeInfo')
        ->with(1)
        ->andReturn([
            'o_result' => [[
                'id'            => 1,
                'name'          => 'Partner Name',
                'contract'      => $contract,
                'country_name'  => 'RUSSIA',
                'rang'          => 5,
                'win_dt_career' => '2024-01-01',
            ]],
        ]);

    $this->repository
        ->shouldReceive('getRankNameById')
        ->with(5)
        ->andReturn('Rank Name');

    $result = $this->useCase->getByContract($contract);

    expect($result)->toBeInstanceOf(PartnerInfo::class)
        ->and($result->getContract())->toBe($contract);
});

it('throws exception when contract not found', function (): void {
    $this->repository
        ->shouldReceive('getEmployeeByContract')
        ->with('INVALID')
        ->andReturn(null);

    expect(fn () => $this->useCase->getByContract('INVALID'))
        ->toThrow(PartnerDomainException::class, 'Контракт не найден');
});

it('throws exception when access not allowed', function (): void {
    $user = createSecurityUser(999, 'test', 'test');
    $useCase = new PartnerInfoUseCase($this->repository, $user);

    $partnerInfo = new PartnerInfo(
        1,
        'Partner',
        'CONTRACT',
        'Country',
        100,
        5,
        null,
        null,
        'Rank'
    );

    $this->repository
        ->shouldReceive('getEmployeeByContract')
        ->andReturn($partnerInfo);

    expect(fn (): PartnerInfo => $useCase->getByContract('CONTRACT'))
        ->toThrow(PartnerDomainException::class, 'Нет прав на просмотр');
});

it('throws exception when contract is closed', function (): void {
    $partnerInfo = new PartnerInfo(
        1,
        'Partner',
        'CONTRACT',
        'Country',
        643,
        5,
        new DateTimeImmutable('2023-12-31'),
        null,
        'Rank'
    );

    $this->repository
        ->shouldReceive('getEmployeeByContract')
        ->andReturn($partnerInfo);

    expect(fn () => $this->useCase->getByContract('CONTRACT'))
        ->toThrow(PartnerDomainException::class, 'Контракт закрыт');
});

it('allows access for admin user', function (): void {
    $reflection = new ReflectionClass(PartnerInfoUseCase::class);
    $method = $reflection->getMethod('accessAllowed');
    $method->setAccessible(true);

    $result = $method->invoke($this->useCase, 643);

    expect($result)->toBeTrue();
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(PartnerInfoUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('partnerInfoRepository')
        ->and($parameters[1]->getName())->toBe('user');
});

it('has getByContract method', function (): void {
    $reflection = new ReflectionClass(PartnerInfoUseCase::class);

    expect($reflection->hasMethod('getByContract'))->toBeTrue();

    $method = $reflection->getMethod('getByContract');
    expect($method->isPublic())->toBeTrue();
});

it('has protected accessAllowed method', function (): void {
    $reflection = new ReflectionClass(PartnerInfoUseCase::class);

    expect($reflection->hasMethod('accessAllowed'))->toBeTrue();

    $method = $reflection->getMethod('accessAllowed');
    expect($method->isProtected())->toBeTrue();
});
