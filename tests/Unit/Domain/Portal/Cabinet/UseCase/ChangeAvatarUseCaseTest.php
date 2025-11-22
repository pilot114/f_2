<?php

declare(strict_types=1);

use App\Common\Service\File\AvatarService;
use App\Common\Service\File\ImageBase64;
use App\Domain\Portal\Cabinet\UseCase\ChangeAvatarUseCase;
use App\Domain\Portal\Files\Entity\File;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

beforeEach(function (): void {
    $this->avatarService = Mockery::mock(AvatarService::class);
    $this->imageBase64 = Mockery::mock(ImageBase64::class);
    $this->useCase = new ChangeAvatarUseCase(
        $this->avatarService,
        $this->imageBase64
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('успешно меняет аватар пользователя', function (File $existingAvatar): void {
    $userId = 123;
    $base64Image = 'data:image/jpeg;base64,fake_base64_content';
    $mockTempFile = Mockery::mock(SymfonyFile::class);

    $this->imageBase64
        ->shouldReceive('baseToFile')
        ->once()
        ->with($base64Image)
        ->andReturn($mockTempFile);

    $this->avatarService
        ->shouldReceive('getAvatar')
        ->once()
        ->with($userId)
        ->andReturn($existingAvatar);

    $this->avatarService
        ->shouldReceive('commonUpload')
        ->once()
        ->with($mockTempFile, File::USERPIC_COLLECTION, $existingAvatar, $userId, true)
        ->andReturn($existingAvatar);

    $file = $this->useCase->changeAvatar($base64Image, $userId);
    $this->avatarService->shouldHaveReceived('commonUpload');

    $this->assertInstanceOf(File::class, $file);

    Mockery::close();
})->with('avatar');

it('correctly handles the case when the user does not have an avatar', function (File $newAvatar): void {
    $userId = 123;
    $base64Image = 'data:image/jpeg;base64,fake_base64_content';
    $mockTempFile = Mockery::mock(SymfonyFile::class);

    $this->imageBase64
        ->shouldReceive('baseToFile')
        ->once()
        ->with($base64Image)
        ->andReturn($mockTempFile);

    $this->avatarService
        ->shouldReceive('getAvatar')
        ->once()
        ->with($userId)
        ->andReturn(null);

    $this->avatarService
        ->shouldReceive('commonUpload')
        ->once()
        ->with($mockTempFile, File::USERPIC_COLLECTION, null, $userId, true)
        ->andReturn($newAvatar);

    $this->useCase->changeAvatar($base64Image, $userId);
})->with('avatar');

it('пробрасывает исключения от ImageBase64::baseToFile', function (): void {
    $userId = 123;
    $base64Image = 'invalid_base64_content';
    $expectedException = new BadRequestHttpException('Тестовое исключение');

    $this->imageBase64
        ->shouldReceive('baseToFile')
        ->once()
        ->with($base64Image)
        ->andThrow($expectedException);

    $this->expectException(get_class($expectedException));
    $this->expectExceptionMessage($expectedException->getMessage());

    $this->useCase->changeAvatar($base64Image, $userId);
});

it('пробрасывает исключения от AvatarService::getAvatar', function (): void {
    $userId = 123;
    $base64Image = 'data:image/jpeg;base64,fake_base64_content';
    $mockTempFile = Mockery::mock(SymfonyFile::class);
    $expectedException = new RuntimeException('Ошибка при получении аватара');

    $this->imageBase64
        ->shouldReceive('baseToFile')
        ->once()
        ->with($base64Image)
        ->andReturn($mockTempFile);

    $this->avatarService
        ->shouldReceive('getAvatar')
        ->once()
        ->with($userId)
        ->andThrow($expectedException);

    $this->expectException(get_class($expectedException));
    $this->expectExceptionMessage($expectedException->getMessage());

    $this->useCase->changeAvatar($base64Image, $userId);
});

it('пробрасывает исключения от AvatarService::commonUpload', function (File $avatar): void {
    $userId = 123;
    $base64Image = 'data:image/jpeg;base64,fake_base64_content';
    $mockTempFile = Mockery::mock(SymfonyFile::class);
    $expectedException = new RuntimeException('Ошибка при загрузке файла');

    $this->imageBase64
        ->shouldReceive('baseToFile')
        ->once()
        ->with($base64Image)
        ->andReturn($mockTempFile);

    $this->avatarService
        ->shouldReceive('getAvatar')
        ->once()
        ->with($userId)
        ->andReturn($avatar);

    $this->avatarService
        ->shouldReceive('commonUpload')
        ->once()
        ->with($mockTempFile, File::USERPIC_COLLECTION, $avatar, $userId, true)
        ->andThrow($expectedException);

    $this->expectException(get_class($expectedException));
    $this->expectExceptionMessage($expectedException->getMessage());

    $this->useCase->changeAvatar($base64Image, $userId);
})->with('avatar');
