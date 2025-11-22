<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\File;

use App\Common\Service\File\ImageBase64;
use App\Common\Service\File\TempFileRegistry;
use Mockery;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

beforeEach(function (): void {
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->service = new ImageBase64($this->tempFileRegistry);
});

afterEach(function (): void {
    Mockery::close();
});

it('converts base64 to file', function (): void {
    // Arrange
    $base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    $file = Mockery::mock(File::class);

    $this->tempFileRegistry->shouldReceive('createFile')->andReturn($file);

    // Act
    $result = $this->service->baseToFile($base64);

    // Assert
    expect($result)->toBe($file);
});

it('throws exception for invalid base64', function (): void {
    // Arrange
    $base64 = 'not a base64 image';
    $this->tempFileRegistry->shouldReceive('createFile')->never();

    // Act & Assert
    expect(fn () => $this->service->baseToFile($base64))
        ->toThrow(BadRequestHttpException::class, 'это не изображение в формате base64');
});

it('throws exception for unsupported image type', function (): void {
    // Arrange
    $base64 = 'data:image/unsupport;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    $this->tempFileRegistry->shouldReceive('createFile')->never();

    // Act & Assert
    expect(fn () => $this->service->baseToFile($base64))
        ->toThrow(UnsupportedMediaTypeHttpException::class, 'не подходящий формат изображения');
});

it('throws exception for oversized image', function (): void {
    // Arrange
    $base64 = 'data:image/png;base64,' . str_repeat('a', ImageBase64::MAX_IMAGE_SIZE);
    $this->tempFileRegistry->shouldReceive('createFile')->never();

    // Act & Assert
    expect(fn () => $this->service->baseToFile($base64, 10))
        ->toThrow(BadRequestHttpException::class, 'максимальный размер файла - 8 МБ');
});
