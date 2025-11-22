<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Exception;

use App\Common\Exception\DomainException;
use App\Common\Exception\FileException;

it('creates FileException with message', function (): void {
    // Arrange & Act
    $exception = new FileException('File not found');

    // Assert
    expect($exception->getMessage())->toBe('File not found')
        ->and($exception->getCode())->toBe(DomainException::FILE)
        ->and($exception->getCode())->toBe(DomainException::FILE);
});

it('extends DomainException', function (): void {
    // Arrange
    $exception = new FileException('Test error');

    // Assert
    expect($exception)->toBeInstanceOf(DomainException::class);
});

it('has correct error code', function (): void {
    // Arrange
    $exception = new FileException('Any message');

    // Assert
    expect($exception->getCode())->toBe(603);
});

it('creates different FileExceptions with different messages', function (string $message): void {
    // Arrange & Act
    $exception = new FileException($message);

    // Assert
    expect($exception->getMessage())->toBe($message)
        ->and($exception->getCode())->toBe(DomainException::FILE);
})->with([
    'File upload failed',
    'Invalid file format',
    'File size exceeds limit',
    'Unable to read file',
]);

it('can be caught as DomainException', function (): void {
    // Arrange
    $caught = false;

    try {
        throw new FileException('Test');
    } catch (DomainException $e) {
        $caught = true;
    }

    // Assert
    expect($caught)->toBeTrue();
});

it('has empty context by default', function (): void {
    // Arrange
    $exception = new FileException('Test');

    // Assert
    expect($exception->getContext())->toBe([]);
});
