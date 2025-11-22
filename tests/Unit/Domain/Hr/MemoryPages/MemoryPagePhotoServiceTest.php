<?php

declare(strict_types=1);

use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Files\Repository\FileQueryRepository;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->readRepo = Mockery::mock(FileQueryRepository::class);

    // Create service with mocked repository using reflection
    $reflection = new ReflectionClass(MemoryPagePhotoService::class);
    $this->service = $reflection->newInstanceWithoutConstructor();

    $readRepoProperty = $reflection->getParentClass()->getProperty('readRepo');
    $readRepoProperty->setAccessible(true);
    $readRepoProperty->setValue($this->service, $this->readRepo);
});

afterEach(function (): void {
    Mockery::close();
});

it('gets comment photos by comment id', function (): void {
    $commentId = 123;

    $photo1 = Mockery::mock(File::class);
    $photo2 = Mockery::mock(File::class);
    $photos = new Collection([$photo1, $photo2]);

    $this->readRepo->shouldReceive('findBy')
        ->once()
        ->with([
            'parentid'   => $commentId,
            'parent_tbl' => 'cp_mp_comments',
        ])
        ->andReturn($photos);

    $result = $this->service->getCommentPhotos($commentId);

    expect($result)->toBe($photos)
        ->and($result->count())->toBe(2);
});

it('returns empty collection when no comment photos', function (): void {
    $commentId = 999;
    $emptyCollection = new Collection([]);

    $this->readRepo->shouldReceive('findBy')
        ->once()
        ->with([
            'parentid'   => $commentId,
            'parent_tbl' => 'cp_mp_comments',
        ])
        ->andReturn($emptyCollection);

    $result = $this->service->getCommentPhotos($commentId);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(0);
});

it('gets avatar by user id', function (): void {
    $userId = 42;
    $avatar = Mockery::mock(File::class);

    $this->readRepo->shouldReceive('findOneBy')
        ->once()
        ->with([
            'parentid'   => $userId,
            'parent_tbl' => 'userpic',
        ])
        ->andReturn($avatar);

    $result = $this->service->getAvatar($userId);

    expect($result)->toBe($avatar);
});

it('returns null when avatar not found', function (): void {
    $userId = 999;

    $this->readRepo->shouldReceive('findOneBy')
        ->once()
        ->with([
            'parentid'   => $userId,
            'parent_tbl' => 'userpic',
        ])
        ->andReturn(null);

    $result = $this->service->getAvatar($userId);

    expect($result)->toBeNull();
});

it('uses correct collection constants', function (): void {
    expect(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION)->toBe('cp_mp_main')
        ->and(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION)->toBe('cp_mp_other')
        ->and(MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION)->toBe('cp_mp_comments')
        ->and(MemoryPagePhotoService::USER_AVATAR_COLLECTION)->toBe('userpic');
});
