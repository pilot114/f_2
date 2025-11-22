<?php

declare(strict_types=1);

use App\Common\Service\File\ImageBase64;
use App\Common\Service\File\TempFileRegistry;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

beforeEach(function (): void {
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->imageBase64 = new ImageBase64($this->tempFileRegistry);
});

afterEach(function (): void {
    Mockery::close();
});

it('успешно конвертирует корректное base64 изображение в файл', function (): void {
    $mockFile = Mockery::mock(File::class);
    $validBase64 = 'data:image/jpeg;base64,' . base64_encode('fake_image_content');

    $this->tempFileRegistry
        ->shouldReceive('createFile')
        ->once()
        ->with(Mockery::type('string'))
        ->andReturn($mockFile);

    $result = $this->imageBase64->baseToFile($validBase64);

    expect($result)->toBe($mockFile);
});

it('выбрасывает исключение при некорректном формате base64 строки', function (): void {
    $invalidBase64 = 'invalid_base64_string';

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('это не изображение в формате base64');

    $this->imageBase64->baseToFile($invalidBase64);
});

it('выбрасывает исключение при неподдерживаемом формате изображения', function (): void {
    $unsupportedImageType = 'data:image/unsupported;base64,' . base64_encode('fake_image_content');

    $this->expectException(UnsupportedMediaTypeHttpException::class);
    $this->expectExceptionMessage('не подходящий формат изображения');

    $this->imageBase64->baseToFile($unsupportedImageType);
});

it('выбрасывает исключение при некорректной base64 строке', function (): void {
    $invalidBase64 = 'data:image/jpeg;base64,invalid-base64!#';

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Невалидный base64');

    $this->imageBase64->baseToFile($invalidBase64);
});

it('выбрасывает исключение при превышении максимального размера файла', function (): void {
    $this->imageBase64 = Mockery::mock(ImageBase64::class, [$this->tempFileRegistry])->makePartial();
    $this->imageBase64
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive('getImageSize')
        ->once()
        ->andReturn(ImageBase64::MAX_IMAGE_SIZE + 1);

    $validBase64 = 'data:image/jpeg;base64,' . base64_encode('fake_image_content');

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('максимальный размер файла - 8 МБ');

    $this->imageBase64->baseToFile($validBase64);
});

it('успешно обрабатывает изображение с заданным максимальным размером', function (): void {
    $mockFile = Mockery::mock(File::class);
    $validBase64 = 'data:image/jpeg;base64,' . base64_encode('fake_image_content');
    $customMaxSize = 1024 * 1024; // 1 MB

    $this->tempFileRegistry
        ->shouldReceive('createFile')
        ->once()
        ->with(Mockery::type('string'))
        ->andReturn($mockFile);

    $result = $this->imageBase64->baseToFile($validBase64, $customMaxSize);

    expect($result)->toBe($mockFile);
});

it('выбрасывает исключение при превышении пользовательского максимального размера файла', function (): void {
    $customMaxSize = 1024; // 1 KB
    $this->imageBase64 = Mockery::mock(ImageBase64::class, [$this->tempFileRegistry])->makePartial();
    $this->imageBase64
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive('getImageSize')
        ->once()
        ->andReturn($customMaxSize + 1);
    $largeBase64 = 'data:image/jpeg;base64,' . base64_encode('fake_image_content');

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('максимальный размер файла - 8 МБ');

    $this->imageBase64->baseToFile($largeBase64, $customMaxSize);
});

it('успешно обрабатывает все разрешенные типы изображений', function (): void {
    $allowedTypes = ['jpeg', 'png', 'gif'];

    foreach ($allowedTypes as $type) {
        $mockFile = Mockery::mock(File::class);
        $validBase64 = "data:image/$type;base64," . base64_encode('fake_image_content');

        $this->tempFileRegistry
            ->shouldReceive('createFile')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn($mockFile);

        $result = $this->imageBase64->baseToFile($validBase64);
        expect($result)->toBe($mockFile);
    }
});
