<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Portal\Files\Entity\File;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('creates Employee with id and name', function (): void {
    // Arrange & Act
    $employee = new Employee(id: 123, name: 'Иван Иванов', response: []);

    // Assert
    expect($employee->id)->toBe(123)
        ->and($employee->name)->toBe('Иван Иванов');
});

it('returns first response from response array', function (): void {
    // Arrange
    $response = new Response(id: 1, name: 'IT отдел');
    $employee = new Employee(id: 1, name: 'Test', response: [$response]);

    // Act
    $result = $employee->getResponse();

    // Assert
    expect($result)->toBe($response);
});

it('returns null when no response available', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test', response: []);

    // Act
    $result = $employee->getResponse();

    // Assert
    expect($result)->toBeNull();
});

it('sets and includes avatar in toArray', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test', response: []);
    $avatar = Mockery::mock(File::class);
    $avatar->shouldReceive('getImageUrls')->andReturn(['url1', 'url2']);

    // Act
    $employee->setAvatar($avatar);
    $array = $employee->toArray();

    // Assert
    expect($array['avatar'])->toBe(['url1', 'url2']);
});

it('converts to array with response', function (): void {
    // Arrange
    $response = new Response(id: 2, name: 'HR');
    $employee = new Employee(id: 5, name: 'John Doe', response: [$response]);

    // Act
    $array = $employee->toArray();

    // Assert
    expect($array)->toHaveKey('id', 5)
        ->and($array)->toHaveKey('name', 'John Doe')
        ->and($array['response'])->toBe([
            'id'   => 2,
            'name' => 'HR',
        ])
        ->and($array['avatar'])->toBeNull();
});

it('converts to array without response', function (): void {
    // Arrange
    $employee = new Employee(id: 10, name: 'Jane Smith', response: []);

    // Act
    $array = $employee->toArray();

    // Assert
    expect($array['id'])->toBe(10)
        ->and($array['name'])->toBe('Jane Smith')
        ->and($array['response'])->toBeNull()
        ->and($array['avatar'])->toBeNull();
});
