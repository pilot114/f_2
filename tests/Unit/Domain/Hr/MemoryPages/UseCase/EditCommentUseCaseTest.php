<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\EditCommentRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\EditCommentUseCase;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use DomainException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

afterEach(function (): void {
    Mockery::close();
});

it('edits comment successfully when user is author', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Original text'
    );

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Updated text',
        photos: []
    );

    $commentsQueryRepo->shouldReceive('getById')
        ->with(10)
        ->once()
        ->andReturn($comment);

    $secRepo->shouldReceive('hasCpAction')
        ->with(5, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(false);

    $photoService->shouldReceive('getAvatar')
        ->with(5)
        ->once()
        ->andReturn(null);

    $photoService->shouldReceive('getCommentPhotos')
        ->with(10)
        ->once()
        ->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);

    // Assert
    expect($result->getText())->toBe('Updated text');
});

it('throws exception when user is not author and has no rights', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 1,
        name: 'User',
        email: 'user@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Updated text',
        photos: []
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')
        ->with(1, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(false);

    // Act & Assert
    expect(fn (): Comment => $useCase->editComment($request))
        ->toThrow(AccessDeniedHttpException::class, 'Комментарий может редактировать только автор, либо сотрудник с соответствующими правами');
});

it('allows edit when user has admin rights', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 1,
        name: 'Admin',
        email: 'admin@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Updated text',
        photos: []
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')
        ->with(1, 'memory_pages.memory_pages_add')
        ->once()
        ->andReturn(true);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));
    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);

    // Assert
    expect($result->getText())->toBe('Updated text');
});

it('throws exception when too many photos', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    // Add 10 existing photos
    for ($i = 1; $i <= 10; $i++) {
        $photo = Mockery::mock(File::class);
        $photo->shouldReceive('getId')->andReturn($i);
        $comment->addPhoto($photo);
    }

    // Try to add one more
    $photosToAdd = [
        new Photo(id: null, base64: 'newphoto', toDelete: false),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToAdd
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));

    // Act & Assert
    expect(fn (): Comment => $useCase->editComment($request))
        ->toThrow(DomainException::class, 'не должно быть больше 10 дополнительных фото');
});

it('adds new photo to comment', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $photosToAdd = [
        new Photo(id: null, base64: 'base64data', toDelete: false),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToAdd
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));

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
            10
        )
        ->once()
        ->andReturn($uploadedFile);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);

    // Assert
    expect($result->getPhotos())->toContain($uploadedFile);
});

it('deletes photo from comment', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $existingPhoto = Mockery::mock(File::class);
    $existingPhoto->shouldReceive('getId')->andReturn(5);
    $existingPhoto->shouldReceive('getUserId')->andReturn(5);

    $photosToDelete = [
        new Photo(id: 5, base64: null, toDelete: true),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToDelete
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([$existingPhoto]));

    $photoService->shouldReceive('commonDelete')
        ->with(5, 5)
        ->once();

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);

    // Assert
    expect($result->getPhotoById(5))->toBeNull();
});

it('sets employee avatar when available', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Updated text',
        photos: []
    );

    $avatarFile = Mockery::mock(File::class);
    $avatarFile->shouldReceive('getId')->andReturn(100);
    $avatarFile->shouldReceive('getImageUrls')->andReturn([
        'url' => 'avatar.jpg',
    ]);

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')
        ->with(5)
        ->once()
        ->andReturn($avatarFile);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));
    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);
    $employeeArray = $result->getEmployee()->toArray();

    // Assert
    expect($employeeArray['avatar'])->toBe([
        'url' => 'avatar.jpg',
    ]);
});

it('replaces existing photo with new one', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $existingPhoto = Mockery::mock(File::class);
    $existingPhoto->shouldReceive('getId')->andReturn(7);
    $existingPhoto->shouldReceive('getUserId')->andReturn(5);

    $photosToReplace = [
        new Photo(id: 7, base64: 'newbase64data', toDelete: false),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToReplace
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([$existingPhoto]));

    $tempFile = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('newbase64data')
        ->once()
        ->andReturn($tempFile);

    $photoService->shouldReceive('commonDelete')
        ->with(7, 5)
        ->once();

    $newPhoto = Mockery::mock(File::class);
    $newPhoto->shouldReceive('getId')->andReturn(8);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempFile,
            MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
            null,
            10
        )
        ->once()
        ->andReturn($newPhoto);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $commentCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->editComment($request);

    // Assert
    expect($result->getPhotos())->toContain($newPhoto);
});

it('throws exception when photo to replace not found', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $photosToReplace = [
        new Photo(id: 888, base64: 'newbase64', toDelete: false),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToReplace
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();

    // Act & Assert
    expect(fn (): Comment => $useCase->editComment($request))
        ->toThrow(NotFoundHttpException::class, 'фотография с id = 888 не найдена в коллекции фото');
});

it('throws exception when photo to delete not found', function (): void {
    // Arrange
    $commentsQueryRepo = Mockery::mock(CommentsQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $secRepo = Mockery::mock(SecurityQueryRepository::class);
    $currentUser = createSecurityUser(
        id: 5,
        email: 'test@test.com',
    );

    $useCase = new EditCommentUseCase(
        $commentsQueryRepo,
        $commentCommandRepo,
        $photoService,
        $imageBase64,
        $transaction,
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
        text: 'Text'
    );

    $photosToDelete = [
        new Photo(id: 999, base64: null, toDelete: true),
    ];

    $request = new EditCommentRequest(
        commentId: 10,
        text: 'Text',
        photos: $photosToDelete
    );

    $commentsQueryRepo->shouldReceive('getById')->andReturn($comment);
    $secRepo->shouldReceive('hasCpAction')->andReturn(false);
    $photoService->shouldReceive('getAvatar')->andReturn(null);
    $photoService->shouldReceive('getCommentPhotos')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();

    // Act & Assert
    expect(fn (): Comment => $useCase->editComment($request))
        ->toThrow(NotFoundHttpException::class, 'фотография с id = 999 не найдена в коллекции фото');
});
