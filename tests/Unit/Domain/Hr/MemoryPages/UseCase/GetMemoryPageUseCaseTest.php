<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\GetMemoryPageUseCase;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('gets memory page with photos', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoRepo = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new GetMemoryPageUseCase($memoryPageRepo, $photoRepo);

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Short obituary',
        obituaryFull: 'Full obituary',
        comments: [],
        workPeriods: []
    );

    $request = new GetMemoryPageRequest(10);

    $memoryPageRepo->shouldReceive('getItem')
        ->with(Mockery::on(fn ($req): bool => $req->id === 10))
        ->once()
        ->andReturn($memoryPage);

    $mainPhoto = Mockery::mock(File::class);
    $mainPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION);
    $otherPhoto1 = Mockery::mock(File::class);
    $otherPhoto1->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $otherPhoto2 = Mockery::mock(File::class);
    $otherPhoto2->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $photos = collect([$mainPhoto, $otherPhoto1, $otherPhoto2]);

    $photoRepo->shouldReceive('getAllForMemoryPage')
        ->with($memoryPage)
        ->once()
        ->andReturn($photos);

    // Act
    $result = $useCase->getItem($request);

    // Assert
    expect($result->getOtherPhotos())->toHaveCount(2);
});

it('sorts comments and work periods', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoRepo = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new GetMemoryPageUseCase($memoryPageRepo, $photoRepo);

    $employee = new Employee(id: 1, name: 'Test User', response: []);

    $comment1 = new Comment(
        id: 1,
        memoryPageId: 10,
        isPinned: false,
        createDate: new DateTimeImmutable('2025-01-03'),
        employee: $employee,
        text: 'Comment 1'
    );

    $comment2 = new Comment(
        id: 2,
        memoryPageId: 10,
        isPinned: true,
        createDate: new DateTimeImmutable('2025-01-02'),
        employee: $employee,
        text: 'Comment 2'
    );

    $response = new Response(1, 'Test');
    $wp1 = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2015-01-01'),
        endDate: new DateTimeImmutable('2020-12-31'),
        response: $response
    );

    $wp2 = new WorkPeriod(
        id: 2,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2010-01-01'),
        endDate: new DateTimeImmutable('2015-12-31'),
        response: $response
    );

    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Short obituary',
        obituaryFull: 'Full obituary',
        comments: [$comment1, $comment2],
        workPeriods: [$wp1, $wp2]
    );

    $request = new GetMemoryPageRequest(10);

    $memoryPageRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepo->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    // Act
    $result = $useCase->getItem($request);

    // Assert
    $comments = $result->getComments();
    $workPeriods = $result->getWorkPeriods();

    expect($comments[0]->getId())->toBe(2) // pinned first
        ->and($workPeriods[0]->getId())->toBe(2); // earliest end date first
});

it('sets up photos for memory page', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoRepo = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new GetMemoryPageUseCase($memoryPageRepo, $photoRepo);

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Short',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    $request = new GetMemoryPageRequest(10);

    $memoryPageRepo->shouldReceive('getItem')->andReturn($memoryPage);

    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION);
    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $photos = collect([$photo1, $photo2]);

    $photoRepo->shouldReceive('getAllForMemoryPage')
        ->with($memoryPage)
        ->once()
        ->andReturn($photos);

    // Act
    $result = $useCase->getItem($request);

    // Assert
    expect($result->getOtherPhotos())->toHaveCount(1);
});
