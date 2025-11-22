<?php

declare(strict_types=1);

use App\Common\Service\Excel\BaseCommandExcelService;
use App\Common\Service\File\TempFileRegistry;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Service\KpiExcel;
use App\Gateway\WriteExcelGateway;

beforeEach(function (): void {
    $this->writer = Mockery::mock(WriteExcelGateway::class);
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->service = new KpiExcel($this->writer, $this->tempFileRegistry);
});

afterEach(function (): void {
    Mockery::close();
});

it('класс KpiExcel существует', function (): void {
    expect(class_exists('App\Domain\Finance\Kpi\Service\KpiExcel'))->toBeTrue();
});

it('имеет конструктор с зависимостями', function (): void {
    $reflection = new ReflectionClass('App\Domain\Finance\Kpi\Service\KpiExcel');
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();
});

it('extends BaseCommandExcelService', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);

    expect($reflection->getParentClass()->getName())->toBe(BaseCommandExcelService::class);
});

it('has setName method with correct signature', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('setName');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('self');

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(4)
        ->and($parameters[0]->getName())->toBe('monthDate')
        ->and($parameters[1]->getName())->toBe('enterpriseName')
        ->and($parameters[2]->getName())->toBe('kpiType')
        ->and($parameters[3]->getName())->toBe('inRussia');
});

it('setName parameters have correct types', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('setName');

    $parameters = $method->getParameters();
    $monthDateParam = $parameters[0];
    $enterpriseNameParam = $parameters[1];
    $kpiTypeParam = $parameters[2];
    $inRussiaParam = $parameters[3];

    expect($monthDateParam->getType()?->getName())->toBe('DateTimeImmutable')
        ->and($enterpriseNameParam->getType()?->getName())->toBe('string')
        ->and($kpiTypeParam->getType()?->getName())->toBe('App\Domain\Finance\Kpi\Enum\KpiType')
        ->and($inRussiaParam->getType()?->getName())->toBe('bool');
});

it('has setContent method with correct signature', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('setContent');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('self');

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('rows')
        ->and($parameters[0]->getType()?->getName())->toBe('array');
});

it('has protected addKpi method', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('addKpi');

    expect($method->isProtected())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('item')
        ->and($parameters[0]->getType()?->getName())->toBe('array');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('array');
});

it('sets name with monthly KPI type for Russia', function (): void {
    $monthDate = new DateTimeImmutable('2025-10-01');
    $result = $this->service->setName($monthDate, 'Test Enterprise', KpiType::MONTHLY, true);

    expect($result)->toBe($this->service);
});

it('sets name with country suffix when not in Russia', function (): void {
    $monthDate = new DateTimeImmutable('2025-10-01');
    $result = $this->service->setName($monthDate, 'Test Enterprise', KpiType::MONTHLY, false);

    expect($result)->toBe($this->service);
});

it('sets content and processes rows', function (): void {
    $rows = [
        [
            'last_name'         => 'Ivanov',
            'first_name'        => 'Ivan',
            'middle_name'       => 'Ivanovich',
            'cfo_contract'      => 'CFO-123',
            'cfo_name'          => 'IT Department',
            'kpi'               => 95,
            'two_month_bonus'   => 90,
            'four_months_bonus' => 88,
            'enterprise_name'   => 'Enterprise',
        ],
    ];

    $this->writer->shouldReceive('setTitle')
        ->once()
        ->with('Департамент')
        ->andReturnSelf();

    $this->writer->shouldReceive('setData')
        ->andReturnSelf();

    $this->writer->shouldReceive('columns')
        ->andThrow(new RuntimeException('OpenSpout does not support column iteration'));

    $this->writer->shouldReceive('indexToLetter')
        ->andReturnUsing(fn ($i): string => chr(65 + $i));

    $this->writer->shouldReceive('setAutoSize')
        ->andReturnSelf();

    $this->writer->shouldReceive('setHeader')
        ->once()
        ->andReturnSelf();

    $result = $this->service->setContent($rows);

    expect($result)->toBe($this->service);
});

it('maps employee data correctly', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('addKpi');
    $method->setAccessible(true);

    $input = [
        'last_name'         => 'Petrov',
        'first_name'        => 'Petr',
        'middle_name'       => 'Petrovich',
        'cfo_contract'      => 'CFO-456',
        'cfo_name'          => 'HR Department',
        'kpi'               => 92,
        'two_month_bonus'   => 88,
        'four_months_bonus' => 85,
        'enterprise_name'   => 'Enterprise 2',
    ];

    $result = $method->invoke($this->service, $input);

    expect($result)->toHaveKey('Фамилия', 'Petrov')
        ->and($result)->toHaveKey('Имя', 'Petr')
        ->and($result)->toHaveKey('Отчество', 'Petrovich')
        ->and($result)->toHaveKey('KPI ежемесячный %', 92)
        ->and($result)->toHaveKey('KPI спринт %', 88)
        ->and($result)->toHaveKey('KPI квартальный %', 85)
        ->and($result)->toHaveKey('Оклад', null)
        ->and($result)->toHaveKey('KPI ежемесячный, к выплате', null);
});

it('includes empty payment fields in output', function (): void {
    $reflection = new ReflectionClass(KpiExcel::class);
    $method = $reflection->getMethod('addKpi');
    $method->setAccessible(true);

    $input = [
        'last_name'         => 'Test',
        'first_name'        => 'User',
        'middle_name'       => 'Middle',
        'cfo_contract'      => 'CFO-1',
        'cfo_name'          => 'Dept',
        'kpi'               => 100,
        'two_month_bonus'   => 100,
        'four_months_bonus' => 100,
        'enterprise_name'   => 'Ent',
    ];

    $result = $method->invoke($this->service, $input);

    expect($result)->toHaveKey('KPI ежемесячный, к выплате')
        ->and($result['KPI ежемесячный, к выплате'])->toBeNull()
        ->and($result)->toHaveKey('KPI спринт, к выплате')
        ->and($result['KPI спринт, к выплате'])->toBeNull()
        ->and($result)->toHaveKey('KPI квартальный, к выплате')
        ->and($result['KPI квартальный, к выплате'])->toBeNull();
});
