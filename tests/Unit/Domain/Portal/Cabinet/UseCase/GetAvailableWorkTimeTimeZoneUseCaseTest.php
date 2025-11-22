<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use App\Domain\Portal\Cabinet\UseCase\GetAvailableWorkTimeTimeZoneUseCase;
use ReflectionClass;

it('returns list of WorkTimeTimeZone cases', function (): void {
    $useCase = new GetAvailableWorkTimeTimeZoneUseCase();

    $result = $useCase->getList();

    expect($result)->toBeArray()
        ->and($result)->toBe(WorkTimeTimeZone::cases());
});

it('has getList method', function (): void {
    $reflection = new ReflectionClass(GetAvailableWorkTimeTimeZoneUseCase::class);

    expect($reflection->hasMethod('getList'))->toBeTrue();

    $method = $reflection->getMethod('getList');
    expect($method->isPublic())->toBeTrue()
        ->and($method->getReturnType()?->getName())->toBe('array');
});

it('has no dependencies', function (): void {
    $reflection = new ReflectionClass(GetAvailableWorkTimeTimeZoneUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->toBeNull();
});

it('can be instantiated without arguments', function (): void {
    $useCase = new GetAvailableWorkTimeTimeZoneUseCase();

    expect($useCase)->toBeInstanceOf(GetAvailableWorkTimeTimeZoneUseCase::class);
});

it('returns non-empty array', function (): void {
    $useCase = new GetAvailableWorkTimeTimeZoneUseCase();

    $result = $useCase->getList();

    expect($result)->not->toBeEmpty();
});

it('returns array of enum cases', function (): void {
    $useCase = new GetAvailableWorkTimeTimeZoneUseCase();

    $result = $useCase->getList();

    foreach ($result as $item) {
        expect($item)->toBeInstanceOf(WorkTimeTimeZone::class);
    }

    expect(true)->toBeTrue();
});
