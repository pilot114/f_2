<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('creates Comment with required parameters', function (): void {
    // Arrange
    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $createDate = new DateTimeImmutable('2025-01-01 10:00:00');

    // Act
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: $createDate,
        employee: $employee,
        text: 'Test comment'
    );

    // Assert
    expect($comment->getId())->toBe(1)
        ->and($comment->memoryPageId)->toBe(100)
        ->and($comment->isPinned())->toBeFalse()
        ->and($comment->getCreateDate())->toBe($createDate)
        ->and($comment->getEmployee())->toBe($employee)
        ->and($comment->getText())->toBe('Test comment');
});

it('sets id', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );

    // Act
    $comment->setId(999);

    // Assert
    expect($comment->getId())->toBe(999);
});

it('sets and gets text', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Original text'
    );

    // Act
    $comment->setText('Updated text');

    // Assert
    expect($comment->getText())->toBe('Updated text');
});

it('sets and checks pinned status', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );

    // Act
    $comment->setIsPinned(true);

    // Assert
    expect($comment->isPinned())->toBeTrue();
});

it('adds photos', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );
    $photo1 = Mockery::mock(File::class);
    $photo2 = Mockery::mock(File::class);

    // Act
    $comment->addPhoto($photo1);
    $comment->addPhoto($photo2);

    // Assert
    expect($comment->getPhotos())->toHaveCount(2)
        ->and($comment->getPhotos())->toContain($photo1, $photo2);
});

it('gets photo by id', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );
    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getId')->andReturn(10);
    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getId')->andReturn(20);

    $comment->addPhoto($photo1);
    $comment->addPhoto($photo2);

    // Act
    $result = $comment->getPhotoById(20);

    // Assert
    expect($result)->toBe($photo2);
});

it('returns null when photo not found', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );
    $photo = Mockery::mock(File::class);
    $photo->shouldReceive('getId')->andReturn(10);
    $comment->addPhoto($photo);

    // Act
    $result = $comment->getPhotoById(999);

    // Assert
    expect($result)->toBeNull();
});

it('removes photo by id', function (): void {
    // Arrange
    $comment = new Comment(
        id: 1,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: new Employee(1, 'Test', []),
        text: 'Text'
    );
    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getId')->andReturn(10);
    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getId')->andReturn(20);

    $comment->addPhoto($photo1);
    $comment->addPhoto($photo2);

    // Act
    $comment->removePhotoPhotos(10);

    // Assert
    expect($comment->getPhotos())->toHaveCount(1)
        ->and($comment->getPhotoById(10))->toBeNull()
        ->and($comment->getPhotoById(20))->toBe($photo2);
});

it('converts to array correctly', function (): void {
    // Arrange
    $response = new Response(1, 'IT');
    $employee = new Employee(id: 5, name: 'John Doe', response: [$response]);
    $createDate = new DateTimeImmutable('2025-01-15T10:30:00+00:00');

    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: true,
        createDate: $createDate,
        employee: $employee,
        text: 'Great memories!'
    );

    $photo = Mockery::mock(File::class);
    $photo->shouldReceive('getId')->andReturn(50);
    $photo->shouldReceive('getImageUrls')->andReturn(['url1', 'url2']);
    $comment->addPhoto($photo);

    // Act
    $array = $comment->toArray();

    // Assert
    expect($array['id'])->toBe(10)
        ->and($array['employee'])->toBeArray()
        ->and($array['employee']['id'])->toBe(5)
        ->and($array['text'])->toBe('Great memories!')
        ->and($array['isPinned'])->toBeTrue()
        ->and($array['createDate'])->toBe($createDate->format(DateTimeImmutable::ATOM))
        ->and($array['photos'])->toHaveCount(1)
        ->and($array['photos'][0]['id'])->toBe(50)
        ->and($array['photos'][0]['urls'])->toBe(['url1', 'url2']);
});
