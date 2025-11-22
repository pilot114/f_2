<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Portal\Excel\Factory;

use App\Domain\Portal\Excel\Factory\WriterFactory;
use OpenSpout\Writer\XLSX\Writer;
use ReflectionClass;

it('creates Writer instance', function (): void {
    $factory = new WriterFactory();

    $result = $factory->create();

    expect($result)->toBeInstanceOf(Writer::class);
});

it('has create method', function (): void {
    $reflection = new ReflectionClass(WriterFactory::class);

    expect($reflection->hasMethod('create'))->toBeTrue();

    $method = $reflection->getMethod('create');
    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe(Writer::class);
});

it('has no dependencies', function (): void {
    $reflection = new ReflectionClass(WriterFactory::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->toBeNull();
});

it('can be instantiated without arguments', function (): void {
    $factory = new WriterFactory();

    expect($factory)->toBeInstanceOf(WriterFactory::class);
});

it('creates new instance each time', function (): void {
    $factory = new WriterFactory();

    $writer1 = $factory->create();
    $writer2 = $factory->create();

    expect($writer1)->not->toBe($writer2)
        ->and($writer1)->toBeInstanceOf(Writer::class)
        ->and($writer2)->toBeInstanceOf(Writer::class);
});

it('returns Writer without configuration', function (): void {
    $factory = new WriterFactory();
    $writer = $factory->create();

    expect($writer)->toBeInstanceOf(Writer::class);
});
