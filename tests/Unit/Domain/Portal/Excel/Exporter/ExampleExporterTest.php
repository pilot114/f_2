<?php

declare(strict_types=1);

use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use App\Domain\Portal\Excel\Exporter\ExampleExporter;

it('extends AbstractExporter', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);

    expect($reflection->getParentClass()->getName())->toBe(AbstractExporter::class);
});

it('has export method with array parameter', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);
    $method = $reflection->getMethod('export');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('params');
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('logger')
        ->and($parameters[1]->getName())->toBe('writer');
});

it('has getExampleData private method', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);

    expect($reflection->hasMethod('getExampleData'))->toBeTrue();

    $method = $reflection->getMethod('getExampleData');
    expect($method->isPrivate())->toBeTrue();
});

it('has getExporterName method', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);

    expect($reflection->hasMethod('getExporterName'))->toBeTrue();
});

it('has getFileName method', function (): void {
    $reflection = new ReflectionClass(ExampleExporter::class);

    expect($reflection->hasMethod('getFileName'))->toBeTrue();
});
