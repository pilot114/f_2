<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\AddCommentRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\AddCommentUseCase;
use App\Domain\Portal\Files\Entity\File;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

afterEach(function (): void {
    Mockery::close();
});

it('adds comment successfully', function (): void {
    // Arrange
    $commentRepo = Mockery::mock(CommentCommandRepository::class);
    $pageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $currentUser = createSecurityUser(
        id: 1,
        email: 'test@test.com',
    );

    $useCase = new AddCommentUseCase(
        $commentRepo,
        $pageRepo,
        $currentUser,
        $imageBase64,
        $photoService
    );

    $request = new AddCommentRequest(
        memoryPageId: 100,
        text: 'Great person!',
        photos: []
    );

    $pageRepo->shouldReceive('count')
        ->with([
            'id' => 100,
        ])
        ->once()
        ->andReturn(1);

    $photoService->shouldReceive('getAvatar')
        ->with(1)
        ->once()
        ->andReturn(null);

    $commentRepo->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (Comment $comment): Comment {
            $comment->setId(1);
            return $comment;
        });

    // Act
    $result = $useCase->addComment($request);

    // Assert
    expect($result)->toBeInstanceOf(Comment::class)
        ->and($result->getText())->toBe('Great person!')
        ->and($result->memoryPageId)->toBe(100);
});

it('throws exception when memory page not found', function (): void {
    // Arrange
    $commentRepo = Mockery::mock(CommentCommandRepository::class);
    $pageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $currentUser = createSecurityUser(
        id: 1,
        name: 'Test',
        email: 'test@test.com',
    );

    $useCase = new AddCommentUseCase(
        $commentRepo,
        $pageRepo,
        $currentUser,
        $imageBase64,
        $photoService
    );

    $request = new AddCommentRequest(
        memoryPageId: 999,
        text: 'Comment',
        photos: []
    );

    $pageRepo->shouldReceive('count')
        ->with([
            'id' => 999,
        ])
        ->once()
        ->andReturn(0);

    // Act & Assert
    expect(fn (): Comment => $useCase->addComment($request))
        ->toThrow(NotFoundHttpException::class, 'не найдена страница памяти с id = 999');
});

it('adds comment with photos', function (): void {
    // Arrange
    $commentRepo = Mockery::mock(CommentCommandRepository::class);
    $pageRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $currentUser = createSecurityUser(
        id: 1,
        email: 'test@test.com',
    );

    $useCase = new AddCommentUseCase(
        $commentRepo,
        $pageRepo,
        $currentUser,
        $imageBase64,
        $photoService
    );

    $photo = new Photo(id: null, base64: 'base64data', toDelete: false);
    $request = new AddCommentRequest(
        memoryPageId: 100,
        text: 'Comment with photo',
        photos: [$photo]
    );

    $pageRepo->shouldReceive('count')->andReturn(1);
    $photoService->shouldReceive('getAvatar')->andReturn(null);

    $commentRepo->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (Comment $comment): Comment {
            $comment->setId(5);
            return $comment;
        });

    $tempFile = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('base64data')
        ->once()
        ->andReturn($tempFile);

    $uploadedFile = Mockery::mock(File::class);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempFile,
            MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
            null,
            5
        )
        ->once()
        ->andReturn($uploadedFile);

    // Act
    $result = $useCase->addComment($request);

    // Assert
    expect($result->getPhotos())->toHaveCount(1)
        ->and($result->getPhotos()[0])->toBe($uploadedFile);
});
