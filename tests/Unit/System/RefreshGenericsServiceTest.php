<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\System\RefreshGenericsService;
use ReflectionClass;

it('имеет необходимые зависимости', function (): void {
    $reflection = new ReflectionClass(RefreshGenericsService::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(4)
        ->and($parameters[0]->getName())->toBe('phpStanExtractor')
        ->and($parameters[1]->getName())->toBe('fileLoader')
        ->and($parameters[2]->getName())->toBe('filesystem')
        ->and($parameters[3]->getName())->toBe('projectDir');
});

it('имеет метод writeFile', function (): void {
    $reflection = new ReflectionClass(RefreshGenericsService::class);

    expect($reflection->hasMethod('writeFile'))->toBeTrue();

    $method = $reflection->getMethod('writeFile');

    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('void');
});

it('имеет защищённые массивы для сервисов', function (): void {
    $reflection = new ReflectionClass(RefreshGenericsService::class);

    expect($reflection->hasProperty('repoServices'))->toBeTrue()
        ->and($reflection->hasProperty('useCaseServices'))->toBeTrue();
});

it('имеет метод extractServicesFromGeneric', function (): void {
    $reflection = new ReflectionClass(RefreshGenericsService::class);

    expect($reflection->hasMethod('extractServicesFromGeneric'))->toBeTrue();

    $method = $reflection->getMethod('extractServicesFromGeneric');

    expect($method->getNumberOfParameters())->toBe(2);
});
