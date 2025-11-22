<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use DateTimeImmutable;

it('creates WorkPeriod with required parameters', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2020-01-01');
    $endDate = new DateTimeImmutable('2023-12-31');
    $response = new Response(id: 1, name: 'IT');

    // Act
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 100,
        startDate: $startDate,
        endDate: $endDate,
        response: $response,
        achievements: null
    );

    // Assert
    expect($workPeriod->getId())->toBe(1)
        ->and($workPeriod->memoryPageId)->toBe(100)
        ->and($workPeriod->getStartDate())->toBe($startDate)
        ->and($workPeriod->getEndDate())->toBe($endDate)
        ->and($workPeriod->getResponse())->toBe($response)
        ->and($workPeriod->getAchievements())->toBeNull();
});

it('creates WorkPeriod with achievements', function (): void {
    // Arrange
    $response = new Response(id: 1, name: 'Dev');
    $achievements = 'Разработка нового модуля';

    // Act
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: $response,
        achievements: $achievements
    );

    // Assert
    expect($workPeriod->getAchievements())->toBe($achievements);
});

it('sets and gets start date', function (): void {
    // Arrange
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: new Response(1, 'Test'),
    );
    $newStartDate = new DateTimeImmutable('2019-06-15');

    // Act
    $workPeriod->setStartDate($newStartDate);

    // Assert
    expect($workPeriod->getStartDate())->toBe($newStartDate);
});

it('sets and gets end date', function (): void {
    // Arrange
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: new Response(1, 'Test'),
    );
    $newEndDate = new DateTimeImmutable('2025-12-31');

    // Act
    $workPeriod->setEndDate($newEndDate);

    // Assert
    expect($workPeriod->getEndDate())->toBe($newEndDate);
});

it('sets and gets response', function (): void {
    // Arrange
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: new Response(1, 'Old'),
    );
    $newResponse = new Response(2, 'New Department');

    // Act
    $workPeriod->setResponse($newResponse);

    // Assert
    expect($workPeriod->getResponse())->toBe($newResponse);
});

it('sets and gets achievements', function (): void {
    // Arrange
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: new Response(1, 'Test'),
        achievements: 'Old achievements'
    );

    // Act
    $workPeriod->setAchievements('New achievements');

    // Assert
    expect($workPeriod->getAchievements())->toBe('New achievements');
});

it('sets id', function (): void {
    // Arrange
    $workPeriod = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2021-01-01'),
        response: new Response(1, 'Test'),
    );

    // Act
    $workPeriod->setId(999);

    // Assert
    expect($workPeriod->getId())->toBe(999);
});

it('converts to array correctly', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2020-01-01T10:00:00+00:00');
    $endDate = new DateTimeImmutable('2023-12-31T15:30:00+00:00');
    $response = new Response(id: 5, name: 'Marketing');

    $workPeriod = new WorkPeriod(
        id: 10,
        memoryPageId: 100,
        startDate: $startDate,
        endDate: $endDate,
        response: $response,
        achievements: 'Test achievements'
    );

    // Act
    $array = $workPeriod->toArray();

    // Assert
    expect($array['id'])->toBe(10)
        ->and($array['memoryPageId'])->toBe(100)
        ->and($array['startDate'])->toBe($startDate->format(DateTimeImmutable::ATOM))
        ->and($array['endDate'])->toBe($endDate->format(DateTimeImmutable::ATOM))
        ->and($array['response'])->toBe($response)
        ->and($array['achievements'])->toBe('Test achievements');
});
