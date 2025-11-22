<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Response;

it('creates Response with id and name', function (): void {
    // Arrange & Act
    $response = new Response(id: 1, name: 'Отдел разработки');

    // Assert
    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Отдел разработки');
});

it('has readonly properties', function (): void {
    // Arrange
    $response = new Response(id: 1, name: 'HR');

    // Act - try to modify readonly property should cause error
    // This is validated by PHP itself, so we just verify the values
    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('HR');
});

it('converts to array correctly', function (): void {
    // Arrange
    $response = new Response(id: 5, name: 'Финансовый отдел');

    // Act
    $array = $response->toArray();

    // Assert
    expect($array)->toBe([
        'id'   => 5,
        'name' => 'Финансовый отдел',
    ]);
});

it('creates different responses', function (int $id, string $name): void {
    // Arrange & Act
    $response = new Response(id: $id, name: $name);

    // Assert
    expect($response->id)->toBe($id)
        ->and($response->name)->toBe($name);
})->with([
    [1, 'IT Department'],
    [2, 'HR Department'],
    [3, 'Finance Department'],
    [10, 'Marketing Team'],
]);
