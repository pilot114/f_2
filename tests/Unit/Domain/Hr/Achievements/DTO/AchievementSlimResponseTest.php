<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\DTO;

use App\Domain\Hr\Achievements\DTO\AchievementSlimResponse;

it('creates achievement slim response with all fields', function (): void {
    $response = new AchievementSlimResponse(
        id: 1,
        name: 'Best Employee',
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Best Employee');
});

it('handles different ids', function (): void {
    $response = new AchievementSlimResponse(id: 999, name: 'Test');

    expect($response->id)->toBe(999);
});

it('handles cyrillic names', function (): void {
    $response = new AchievementSlimResponse(
        id: 1,
        name: 'Лучший сотрудник',
    );

    expect($response->name)->toBe('Лучший сотрудник');
});

it('handles long names', function (): void {
    $name = 'This is a very long achievement name that describes an exceptional performance';
    $response = new AchievementSlimResponse(id: 1, name: $name);

    expect($response->name)->toBe($name);
});

it('handles special characters in name', function (): void {
    $response = new AchievementSlimResponse(
        id: 1,
        name: 'Achievement & Trophy!',
    );

    expect($response->name)->toBe('Achievement & Trophy!');
});
