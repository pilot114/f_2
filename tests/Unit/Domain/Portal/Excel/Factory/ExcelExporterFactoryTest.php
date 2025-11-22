<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Portal\Excel\Factory;

use App\Common\Service\Excel\ExcelExporterInterface;
use App\Domain\Portal\Excel\Factory\ExcelExporterFactory;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(MockeryPHPUnitIntegration::class);

it('creates exporter by name', function (): void {
    $exporter1 = Mockery::mock(ExcelExporterInterface::class);
    $exporter1->shouldReceive('getExporterName')->andReturn('ExampleExporter');

    $factory = new ExcelExporterFactory([$exporter1]);

    $result = $factory->create('ExampleExporter');

    expect($result)->toBe($exporter1);
});

it('throws exception when exporter not found', function (): void {
    $factory = new ExcelExporterFactory([]);

    expect(fn (): ExcelExporterInterface => $factory->create('NonExistentExporter'))
        ->toThrow(NotFoundHttpException::class, "Экспортер 'NonExistentExporter' не найден");
});

it('validates unique exporter names', function (): void {
    $exporter1 = Mockery::mock(ExcelExporterInterface::class);
    $exporter1->shouldReceive('getExporterName')->andReturn('DuplicateExporter');

    $exporter2 = Mockery::mock(ExcelExporterInterface::class);
    $exporter2->shouldReceive('getExporterName')->andReturn('DuplicateExporter');

    expect(fn (): ExcelExporterFactory => new ExcelExporterFactory([$exporter1, $exporter2]))
        ->toThrow(InvalidArgumentException::class, 'Дублирующееся имя экспортера');
});

it('accepts multiple unique exporters', function (): void {
    $exporter1 = Mockery::mock(ExcelExporterInterface::class);
    $exporter1->shouldReceive('getExporterName')->andReturn('Exporter1');

    $exporter2 = Mockery::mock(ExcelExporterInterface::class);
    $exporter2->shouldReceive('getExporterName')->andReturn('Exporter2');

    $factory = new ExcelExporterFactory([$exporter1, $exporter2]);

    expect($factory->create('Exporter1'))->toBe($exporter1)
        ->and($factory->create('Exporter2'))->toBe($exporter2);
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(ExcelExporterFactory::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('exporters');
});

it('has create method', function (): void {
    $reflection = new ReflectionClass(ExcelExporterFactory::class);

    expect($reflection->hasMethod('create'))->toBeTrue();

    $method = $reflection->getMethod('create');
    expect($method->isPublic())->toBeTrue();
});

it('has validateUniqueExporterNames private method', function (): void {
    $reflection = new ReflectionClass(ExcelExporterFactory::class);

    expect($reflection->hasMethod('validateUniqueExporterNames'))->toBeTrue();

    $method = $reflection->getMethod('validateUniqueExporterNames');
    expect($method->isPrivate())->toBeTrue();
});
