<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\DTO;

use App\Domain\Hr\Achievements\DTO\EmployeeResponse;

it('creates employee response with all fields', function (): void {
    $response = new EmployeeResponse(
        id: 1,
        name: 'John Doe',
        positionName: 'Senior Developer',
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('John Doe')
        ->and($response->positionName)->toBe('Senior Developer');
});

it('handles different ids', function (): void {
    $response = new EmployeeResponse(
        id: 999,
        name: 'Test User',
        positionName: 'Manager',
    );

    expect($response->id)->toBe(999);
});

it('handles cyrillic names', function (): void {
    $response = new EmployeeResponse(
        id: 1,
        name: 'Иван Иванов',
        positionName: 'Менеджер',
    );

    expect($response->name)->toBe('Иван Иванов')
        ->and($response->positionName)->toBe('Менеджер');
});

it('handles empty position name', function (): void {
    $response = new EmployeeResponse(
        id: 1,
        name: 'Test User',
        positionName: '',
    );

    expect($response->positionName)->toBe('');
});

it('handles long names', function (): void {
    $name = 'John Alexander Michael Christopher David';
    $response = new EmployeeResponse(
        id: 1,
        name: $name,
        positionName: 'Developer',
    );

    expect($response->name)->toBe($name);
});

it('handles long position names', function (): void {
    $position = 'Senior Lead Full Stack Software Engineer and Architect';
    $response = new EmployeeResponse(
        id: 1,
        name: 'Test',
        positionName: $position,
    );

    expect($response->positionName)->toBe($position);
});

it('handles special characters in name', function (): void {
    $response = new EmployeeResponse(
        id: 1,
        name: "O'Brien-Smith",
        positionName: 'Developer',
    );

    expect($response->name)->toBe("O'Brien-Smith");
});
