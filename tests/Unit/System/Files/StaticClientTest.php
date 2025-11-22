<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Files;

use App\Common\Exception\StaticException;
use App\Common\Service\File\TempFileRegistry;
use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Enum\ImageSize;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

beforeEach(function (): void {
    $this->url = 'https://static-service.example.com';
    $this->secret = 'test-secret-key';
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->user = createSecurityUser(4026, 'test', 'test@sibvaleo.com');
    $this->tmpFileRegistry = Mockery::mock(TempFileRegistry::class);

    $this->staticClient = new StaticClient(
        $this->url,
        $this->secret,
        $this->httpClient,
        $this->user,
        $this->tmpFileRegistry
    );

    $this->file = Mockery::mock(File::class);
    $this->file->shouldReceive('getMimeType')->andReturn('text/plain');
});

afterEach(function (): void {
    Mockery::close();
});

function mockSuccessResponse(MockInterface $httpClient, string $uri): void
{
    $successResponse = Mockery::mock(ResponseInterface::class);
    $successResponse->shouldReceive('getContent')
        ->andReturn(json_encode([
            'status' => true,
            'data'   => [
                'uri' => $uri,
            ],
        ]));

    $httpClient->shouldReceive('request')->andReturn($successResponse);
}

function mockFailResponse(MockInterface $httpClient, string $errorMessage): void
{
    $failResponse = Mockery::mock(ResponseInterface::class);
    $failResponse->shouldReceive('getContent')
        ->andReturn(json_encode([
            'status' => false,
            'error'  => $errorMessage,
        ]));

    $httpClient->shouldReceive('request')->andReturn($failResponse);
}

test('uploadFile returns URI when upload is successful', function (): void {
    $this->file->shouldReceive('getSize')->andReturn(1024);
    $this->file->shouldReceive('getPathname')->andReturn('php://memory');
    mockSuccessResponse($this->httpClient, '/public/default/test_file.txt');

    $result = $this->staticClient->uploadFile($this->file, 'test_file.txt');

    expect($result)->toBe('/public/default/test_file.txt');
});

test('uploadFile throw StaticException when upload fails', function (): void {
    $this->file->shouldReceive('getSize')->andReturn(1024);
    $this->file->shouldReceive('getPathname')->andReturn('php://memory');
    mockFailResponse($this->httpClient, 'Some error message');

    $this->expectException(StaticException::class);

    $this->staticClient->uploadFile($this->file, 'test_file.txt');
});

test('removeFile returns true when deletion is successful', function (): void {
    $successResponse = Mockery::mock(ResponseInterface::class);
    $successResponse->shouldReceive('toArray')->with(false)->andReturn([
        'status' => true,
    ]);
    $this->httpClient->shouldReceive('request')->andReturn($successResponse);

    $result = $this->staticClient->removeFile('/public/default/test_file.txt');

    expect($result)->toBeTrue();
});

test('removeFile returns false when deletion fails', function (): void {
    $failResponse = Mockery::mock(ResponseInterface::class);
    $failResponse->shouldReceive('toArray')->with(false)->andReturn([
        'status' => false,
        'error'  => 'File not found',
    ]);
    $this->httpClient->shouldReceive('request')->andReturn($failResponse);

    $result = $this->staticClient->removeFile('/public/default/test_file.txt');

    expect($result)->toBeFalse();
});

test('removeFile correctly handles path with public segment', function (): void {
    $successResponse = Mockery::mock(ResponseInterface::class);
    $successResponse->shouldReceive('toArray')->with(false)->andReturn([
        'status' => true,
    ]);
    $this->httpClient->shouldReceive('request')->andReturn($successResponse);

    $result = $this->staticClient->removeFile('/public/public/default/test_file.txt');

    expect($result)->toBeTrue();
});

test('convertToSafeName transliterates Cyrillic characters', function (): void {
    $cyrillicName = 'Привет_Мир.jpg';
    $result = $this->staticClient->convertToSafeName($cyrillicName);
    expect($result)->toBe('privet_mir.jpg');
});

test('convertToSafeName handles file paths', function (): void {
    $filePath = '/path/to/Привет_Мир.jpg';
    $result = $this->staticClient->convertToSafeName($filePath);
    expect($result)->toBe('privet_mir.jpg');
});

test('getUserpicByUserId returns correct URL', function (): void {
    $result = StaticClient::getUserpicByUserId(123, ImageSize::SMALL);
    expect($result)->toBe('https://static.siberianhealth.com/public/cp_userpic/_resize/123_fit_80_80.jpg');
});

test('getResizeUrl returns correct URL', function (): void {
    $result = StaticClient::getResizeUrl(
        'https://static.siberianhealth.com/public/cp_userpic/4026.jpg',
        ImageSize::SMALL,
    );
    expect($result)->toBe('https://static.siberianhealth.com/public/cp_userpic/_resize/4026_fit_80_80.jpg');
});

test('uploadFile with large file performs chunked upload', function (): void {
    // Файл должен быть больше MAX_FILE_SIZE (8MB) для chunked upload
    // Используем минимальный размер для экономии памяти
    $fileSize = StaticClient::MAX_FILE_SIZE + (100 * 1024); // 8MB + 100KB

    $largeFileHandle = tmpfile();
    // Пишем данные небольшими порциями для экономии памяти
    $chunkWriteSize = 1024 * 1024; // 1MB за раз
    $written = 0;
    while ($written < $fileSize) {
        $toWrite = min($chunkWriteSize, $fileSize - $written);
        fwrite($largeFileHandle, str_repeat('a', $toWrite));
        $written += $toWrite;
    }
    rewind($largeFileHandle);
    $meta_data = stream_get_meta_data($largeFileHandle);
    $largeFilePath = $meta_data['uri'];

    $this->file->shouldReceive('getSize')->andReturn($fileSize);
    $this->file->shouldReceive('getPathname')->andReturn($largeFilePath);
    $this->file->shouldReceive('getBasename')->andReturn('large_file.txt');

    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->andReturn('{"status":true,"data":{"chunkNumber":1}}', '{"status":true,"data":{"uri":"\/public\/default\/large_file.txt"}}');

    $this->httpClient->shouldReceive('request')->twice()->andReturn($response);
    $this->tmpFileRegistry->shouldReceive('createFile')->andReturn($this->file);

    $result = $this->staticClient->uploadFile($this->file, 'large_file.txt');

    expect($result)->toBe('/public/default/large_file.txt');
    fclose($largeFileHandle);
})->group('chunked');

test('uploadFile with isRewrite uses original name', function (): void {
    $this->file->shouldReceive('getSize')->andReturn(1024);
    $this->file->shouldReceive('getPathname')->andReturn('php://memory');

    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')->andReturn('{"status":true,"data":{"uri":"\/public\/default\/test_file.txt"}}');

    $this->httpClient->shouldReceive('request')
        ->withArgs(function ($method, $url, array $options): bool {
            return str_contains($options['body'], 'name="data"; filename="test_file.txt"');
        })
        ->andReturn($response);

    $result = $this->staticClient->uploadFile($this->file, 'test_file.txt', '/default/', true);

    expect($result)->toBe('/public/default/test_file.txt');
});

test('uploadFile with resizeSettings sends resizeData', function (): void {
    $this->file->shouldReceive('getSize')->andReturn(1024);
    $this->file->shouldReceive('getPathname')->andReturn('php://memory');
    $resizeSettings = [
        'width'  => 100,
        'height' => 100,
    ];

    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')->andReturn('{"status":true,"data":{"uri":"\/public\/default\/test_file.txt"}}');

    $this->httpClient->shouldReceive('request')
        ->withArgs(function ($method, $url, array $options) use ($resizeSettings): bool {
            return str_contains($options['body'], 'name="resizeData"');
        })
        ->andReturn($response);

    $result = $this->staticClient->uploadFile($this->file, 'test_file.txt', '/default/', false, $resizeSettings);

    expect($result)->toBe('/public/default/test_file.txt');
});

test('uploadFile with large file throws exception on chunk fail', function (): void {
    // Файл должен быть больше MAX_FILE_SIZE для chunked upload
    $fileSize = StaticClient::MAX_FILE_SIZE + (100 * 1024);

    $largeFileHandle = tmpfile();
    // Пишем данные небольшими порциями для экономии памяти
    $chunkWriteSize = 1024 * 1024; // 1MB за раз
    $written = 0;
    while ($written < $fileSize) {
        $toWrite = min($chunkWriteSize, $fileSize - $written);
        fwrite($largeFileHandle, str_repeat('b', $toWrite));
        $written += $toWrite;
    }
    rewind($largeFileHandle);
    $meta_data = stream_get_meta_data($largeFileHandle);
    $largeFilePath = $meta_data['uri'];

    $this->file->shouldReceive('getSize')->andReturn($fileSize);
    $this->file->shouldReceive('getPathname')->andReturn($largeFilePath);
    $this->file->shouldReceive('getBasename')->andReturn('large_file.txt');

    mockFailResponse($this->httpClient, 'Chunk upload failed');
    $this->tmpFileRegistry->shouldReceive('createFile')->andReturn($this->file);

    $this->expectException(StaticException::class);

    $this->staticClient->uploadFile($this->file, 'large_file.txt');
    fclose($largeFileHandle);
})->group('chunked');

test('uploadFile throws exception if file cannot be opened', function (): void {
    $this->file->shouldReceive('getSize')->andReturn(StaticClient::MAX_FILE_SIZE + 1);
    $this->file->shouldReceive('getPathname')->andReturn('/non/existent/file');
    $this->file->shouldReceive('getBasename')->andReturn('file');

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Не удалось открыть файл /non/existent/file для чтения");

    $this->staticClient->uploadFile($this->file, 'file');
});
