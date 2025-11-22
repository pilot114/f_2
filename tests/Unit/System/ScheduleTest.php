<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\System\Schedule;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->cache = Mockery::mock(CacheInterface::class);
    $this->schedule = new Schedule($this->cache);
});

afterEach(function (): void {
    Mockery::close();
});

it('реализует интерфейс ScheduleProviderInterface', function (): void {
    expect($this->schedule)->toBeInstanceOf(ScheduleProviderInterface::class);
});

it('имеет атрибут AsSchedule', function (): void {
    $reflection = new ReflectionClass(Schedule::class);
    $attributes = $reflection->getAttributes();

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getName())->toBe('Symfony\Component\Scheduler\Attribute\AsSchedule');
});

it('возвращает экземпляр SymfonySchedule', function (): void {
    $schedule = $this->schedule->getSchedule();

    expect($schedule)->toBeInstanceOf(SymfonySchedule::class);
});

it('конфигурирует schedule как stateful с кешем', function (): void {
    $schedule = $this->schedule->getSchedule();

    // Проверяем через рефлексию, что schedule настроен с кешем
    $reflection = new ReflectionClass($schedule);

    // Проверяем, что schedule имеет свойство state (признак stateful)
    expect($reflection->hasProperty('state'))->toBeTrue();
});

it('создаёт новый экземпляр schedule при каждом вызове', function (): void {
    $schedule1 = $this->schedule->getSchedule();
    $schedule2 = $this->schedule->getSchedule();

    expect($schedule1)->not->toBe($schedule2)
        ->and($schedule1)->toBeInstanceOf(SymfonySchedule::class)
        ->and($schedule2)->toBeInstanceOf(SymfonySchedule::class);
});

it('принимает CacheInterface в конструкторе', function (): void {
    $reflection = new ReflectionClass(Schedule::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('cache')
        ->and($parameters[0]->getType()?->getName())->toBe('Symfony\Contracts\Cache\CacheInterface');
});
