<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\PinCommentRequest;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\PinCommentUseCase;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('toggles comment pinned status to true', function (): void {
    // Arrange
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new PinCommentUseCase(
        $commentCommandRepo,
        $commentsQueryRepo,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Test comment'
    );

    $request = new PinCommentRequest(commentId: 10, isPinned: true);

    $commentsQueryRepo->shouldReceive('getById')
        ->with(10)
        ->once()
        ->andReturn($comment);

    $photoService->shouldReceive('getCommentPhotos')
        ->with(10)
        ->once()
        ->andReturn(collect([]));

    $photoService->shouldReceive('getAvatar')
        ->with(1)
        ->once()
        ->andReturn(null);

    $commentCommandRepo->shouldReceive('update')
        ->once()
        ->with($comment);

    // Act
    $result = $useCase->togglePinned($request);

    // Assert
    expect($result->isPinned())->toBeTrue();
});

it('toggles comment pinned status to false', function (): void {
    // Arrange
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new PinCommentUseCase(
        $commentCommandRepo,
        $commentsQueryRepo,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: true,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Pinned comment'
    );

    $request = new PinCommentRequest(commentId: 10, isPinned: false);

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->togglePinned($request);

    // Assert
    expect($result->isPinned())->toBeFalse();
});

it('loads comment photos', function (): void {
    // Arrange
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new PinCommentUseCase(
        $commentCommandRepo,
        $commentsQueryRepo,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Comment'
    );

    $photo1 = Mockery::mock(File::class);
    $photo2 = Mockery::mock(File::class);

    $request = new PinCommentRequest(commentId: 10, isPinned: true);

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $photoService->shouldReceive('getCommentPhotos')
        ->with(10)
        ->once()
        ->andReturn(collect([$photo1, $photo2]));
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->togglePinned($request);

    // Assert
    expect($result->getPhotos())->toHaveCount(2)
        ->and($result->getPhotos())->toContain($photo1, $photo2);
});

it('loads employee avatar', function (): void {
    // Arrange
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new PinCommentUseCase(
        $commentCommandRepo,
        $commentsQueryRepo,
        $photoService
    );

    $employee = new Employee(id: 5, name: 'Test', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Comment'
    );

    $avatar = Mockery::mock(File::class);
    $request = new PinCommentRequest(commentId: 10, isPinned: true);

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));
    $photoService->shouldReceive('getAvatar')
        ->with(5)
        ->once()
        ->andReturn($avatar);
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->togglePinned($request);

    // Assert
    expect($result->getEmployee())->toBe($employee);
});
