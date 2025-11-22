<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\File;

use App\Common\Service\File\TempFileRegistry;

it('creates temp file', function (): void {
    // Arrange
    $service = new TempFileRegistry();
    $content = 'test content';

    // Act
    $file = $service->createFile($content);

    // Assert
    expect($file->getContent())->toBe($content);
    expect(file_exists($file->getPathname()))->toBeTrue();

    // Cleanup
    $service->clear();
});

it('clears temp files', function (): void {
    // Arrange
    $service = new TempFileRegistry();
    $file = $service->createFile('test');
    $path = $file->getPathname();

    // Act
    $service->clear();

    // Assert
    expect(file_exists($path))->toBeFalse();
});

it('creates uploaded file with readable name', function (): void {
    // Arrange
    $service = new TempFileRegistry();
    $content = 'uploaded content';
    $readableName = 'document.pdf';

    // Act
    $file = $service->createUploadedFile($readableName, $content);

    // Assert
    expect($file->getClientOriginalName())->toBe($readableName);
    expect($file->getContent())->toBe($content);
    expect(file_exists($file->getPathname()))->toBeTrue();

    // Cleanup
    $service->clear();
});

it('creates multiple temp files', function (): void {
    // Arrange
    $service = new TempFileRegistry();

    // Act
    $file1 = $service->createFile('content1');
    $file2 = $service->createFile('content2');
    $file3 = $service->createUploadedFile('test.txt', 'content3');

    // Assert
    expect(file_exists($file1->getPathname()))->toBeTrue();
    expect(file_exists($file2->getPathname()))->toBeTrue();
    expect(file_exists($file3->getPathname()))->toBeTrue();
    expect($file1->getPathname())->not->toBe($file2->getPathname());

    // Cleanup
    $service->clear();
    expect(file_exists($file1->getPathname()))->toBeFalse();
    expect(file_exists($file2->getPathname()))->toBeFalse();
    expect(file_exists($file3->getPathname()))->toBeFalse();
});
