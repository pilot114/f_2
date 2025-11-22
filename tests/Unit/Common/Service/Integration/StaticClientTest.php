<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Integration;

use App\Common\Service\File\TempFileRegistry;
use App\Common\Service\Integration\StaticClient;
use Mockery;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

beforeEach(function (): void {
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->currentUser = createSecurityUser(123, 'test', 'test@test.com');
    $this->service = new StaticClient(
        'http://static.example.com',
        'secret',
        $this->httpClient,
        $this->currentUser,
        Mockery::mock(TempFileRegistry::class),
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('uploads file', function (): void {
    // Arrange
    $file = new File(tempnam(sys_get_temp_dir(), 'test'), false);
    file_put_contents($file->getPathname(), 'test content');

    $response = Mockery::mock(ResponseInterface::class);

    $this->httpClient->shouldReceive('request')->andReturn($response);
    $response->shouldReceive('getContent')->andReturn(json_encode([
        'status' => true,
        'data'   => [
            'uri' => '/path/to/file.jpg',
        ],
    ]));

    // Act
    $uri = $this->service->uploadFile($file, 'test.jpg');

    // Assert
    expect($uri)->toBe('/path/to/file.jpg');
});

it('removes file', function (): void {
    // Arrange
    $response = Mockery::mock(ResponseInterface::class);

    $this->httpClient->shouldReceive('request')->andReturn($response);
    $response->shouldReceive('toArray')->andReturn([
        'status' => true,
    ]);

    // Act
    $result = $this->service->removeFile('/public/path/to/file.jpg');

    // Assert
    expect($result)->toBeTrue();
});

it('converts to safe name', function (string $original, string $expected): void {
    // Act
    $safeName = $this->service->convertToSafeName($original);

    // Assert
    expect($safeName)->toBe($expected);
})->with([
    ['имя файла.txt', 'imya_fayla.txt'],
    ['file with spaces.jpg', 'file_with_spaces.jpg'],
    ['special-chars!@#$.png', 'special-chars.png'],
]);
