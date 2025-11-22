<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\DeleteCommentUseCase;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use DateTimeImmutable;
use Mockery;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

afterEach(function (): void {
    Mockery::close();
});

it('deletes comment when user is author', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new DeleteCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $secRepo,
        $currentUser
    );

    $employee = new Employee(id: 5, name: 'Test User', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'My comment'
    );

    $commentsQueryRepo->shouldReceive('getById')
        ->with(10)
        ->once()
        ->andReturn($comment);

    $secRepo->shouldReceive('hasCpAction')
        ->with(5, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(false);

    $photoService->shouldReceive('getCommentPhotos')
        ->with(10)
        ->once()
        ->andReturn(collect([]));

    $commentCommandRepo->shouldReceive('delete')
        ->with(10)
        ->once();

    // Act
    $useCase->deleteComment(10);

    // Assert - Mockery will verify expectations
    expect(true)->toBeTrue();
});

it('deletes comment when user has admin rights', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 1,
        name: 'Admin',
        email: 'admin@test.com',
    );

    $useCase = new DeleteCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $secRepo,
        $currentUser
    );

    $employee = new Employee(id: 5, name: 'Other User', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Comment'
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')
        ->with(1, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(true);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));
    $commentCommandRepo->shouldReceive('delete')->once();

    // Act
    $useCase->deleteComment(10);

    // Assert
    expect(true)->toBeTrue();
});

it('throws exception when user is not author and has no rights', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 1,
        name: 'User',
        email: 'user@test.com',
    );

    $useCase = new DeleteCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $secRepo,
        $currentUser
    );

    $employee = new Employee(id: 5, name: 'Other User', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Comment'
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')
        ->with(1, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(false);

    // Act & Assert
    expect(fn () => $useCase->deleteComment(10))
        ->toThrow(AccessDeniedHttpException::class, 'Комментарий может удалить только автор, либо сотрудник с соответствующими правами');
});

it('deletes comment photos', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new DeleteCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $secRepo,
        $currentUser
    );

    $employee = new Employee(id: 5, name: 'Test User', response: []);
    $comment = new Comment(
        id: 10,
        memoryPageId: 100,
        isPinned: false,
        createDate: new DateTimeImmutable(),
        employee: $employee,
        text: 'Comment'
    );

    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getId')->andReturn(1);
    $photo1->shouldReceive('getUserId')->andReturn(5);

    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getId')->andReturn(2);
    $photo2->shouldReceive('getUserId')->andReturn(5);

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getCommentPhotos')
        ->with(10)
        ->once()
        ->andReturn(collect([$photo1, $photo2]));

    $photoService->shouldReceive('commonDelete')
        ->with(1, 5)
        ->once();
    $photoService->shouldReceive('commonDelete')
        ->with(2, 5)
        ->once();

    $commentCommandRepo->shouldReceive('delete')->once();

    // Act
    $useCase->deleteComment(10);

    // Assert
    expect(true)->toBeTrue();
});
