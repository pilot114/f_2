<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\File;

use App\Common\Service\File\AvatarService;
use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Files\Repository\FileCommandRepository;
use App\Domain\Portal\Files\Repository\FileQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Mockery;

beforeEach(function (): void {
    $this->client = Mockery::mock(StaticClient::class);
    $this->currentUser = Mockery::mock(SecurityUser::class);
    $this->readRepo = Mockery::mock(FileQueryRepository::class);
    $this->writeRepo = Mockery::mock(FileCommandRepository::class);

    $this->service = new AvatarService(
        $this->client,
        $this->currentUser,
        $this->readRepo,
        $this->writeRepo
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns avatar file when found', function (): void {
    // Arrange
    $userId = 123;
    $avatarFile = new File(1, 'test.jpg', '/path/to/file.jpg', 123, 1, File::USERPIC_COLLECTION, 'jpg');

    $this->readRepo->shouldReceive('findOneBy')
        ->once()
        ->with([
            'parentid'   => $userId,
            'parent_tbl' => File::USERPIC_COLLECTION,
        ])
        ->andReturn($avatarFile);

    // Act
    $result = $this->service->getAvatar($userId);

    // Assert
    expect($result)->toBe($avatarFile);
});

it('returns null when avatar file not found', function (): void {
    // Arrange
    $userId = 456;

    $this->readRepo->shouldReceive('findOneBy')
        ->once()
        ->with([
            'parentid'   => $userId,
            'parent_tbl' => File::USERPIC_COLLECTION,
        ])
        ->andReturn(null);

    // Act
    $result = $this->service->getAvatar($userId);

    // Assert
    expect($result)->toBeNull();
});
