<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPageListItem;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Portal\Files\Entity\File;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('creates MemoryPageListItem with required parameters', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'John Doe', response: []);
    $response = new Response(id: 1, name: 'IT Department');

    // Act
    $item = new MemoryPageListItem(
        id: 100,
        employee: $employee,
        mainPhotoId: 50,
        obituary: 'Short obituary text',
        response: $response,
        commentsCount: 5
    );

    // Assert
    expect($item->id)->toBe(100)
        ->and($item->employee)->toBe($employee)
        ->and($item->mainPhotoId)->toBe(50)
        ->and($item->obituary)->toBe('Short obituary text')
        ->and($item->response)->toBe($response)
        ->and($item->commentsCount)->toBe(5);
});

it('creates MemoryPageListItem with zero comments by default', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test', response: []);
    $response = new Response(id: 1, name: 'Test Dept');

    // Act
    $item = new MemoryPageListItem(
        id: 1,
        employee: $employee,
        mainPhotoId: 1,
        obituary: 'Text',
        response: $response
    );

    // Assert
    expect($item->commentsCount)->toBe(0);
});

it('sets and gets main photo', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test', response: []);
    $response = new Response(id: 1, name: 'Test');
    $item = new MemoryPageListItem(
        id: 1,
        employee: $employee,
        mainPhotoId: 10,
        obituary: 'Text',
        response: $response
    );

    $photo = Mockery::mock(File::class);

    // Act
    $item->setMainPhoto($photo);

    // Assert
    expect($item->getMainPhoto())->toBe($photo);
});

it('main photo is null initially', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test', response: []);
    $response = new Response(id: 1, name: 'Test');

    // Act
    $item = new MemoryPageListItem(
        id: 1,
        employee: $employee,
        mainPhotoId: 10,
        obituary: 'Text',
        response: $response
    );

    // Assert
    expect($item->getMainPhoto())->toBeNull();
});

it('converts to array with main photo', function (): void {
    // Arrange
    $employee = new Employee(id: 5, name: 'Jane Smith', response: []);
    $response = new Response(id: 2, name: 'HR');
    $item = new MemoryPageListItem(
        id: 10,
        employee: $employee,
        mainPhotoId: 20,
        obituary: 'Obituary text',
        response: $response,
        commentsCount: 3
    );

    $photo = Mockery::mock(File::class);
    $photo->shouldReceive('getImageUrls')->andReturn(['url1', 'url2']);
    $item->setMainPhoto($photo);

    // Act
    $array = $item->toArray();

    // Assert
    expect($array['id'])->toBe(10)
        ->and($array['name'])->toBe('Jane Smith')
        ->and($array['mainPhoto']['id'])->toBe(20)
        ->and($array['mainPhoto']['urls'])->toBe(['url1', 'url2'])
        ->and($array['obituary'])->toBe('Obituary text')
        ->and($array['response'])->toBe('HR')
        ->and($array['commentsCount'])->toBe(3);
});

it('converts to array without main photo', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Marketing');
    $item = new MemoryPageListItem(
        id: 1,
        employee: $employee,
        mainPhotoId: 5,
        obituary: 'Text',
        response: $response,
        commentsCount: 0
    );

    // Act
    $array = $item->toArray();

    // Assert
    expect($array['mainPhoto']['urls'])->toBeNull();
});
