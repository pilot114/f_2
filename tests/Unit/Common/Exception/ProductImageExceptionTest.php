<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Exception;

use App\Common\Exception\ProductImageException;
use Exception;
use ReflectionClass;

it('creates ProductImageException with message', function (): void {
    // Arrange & Act
    $exception = new ProductImageException('Connection failed');

    // Assert
    expect($exception->getMessage())->toBe('Ошибка получения изображений: Connection failed')
        ->and($exception->getCode())->toBe(400);
});

it('extends Exception', function (): void {
    // Arrange
    $exception = new ProductImageException('Test error');

    // Assert
    expect($exception)->toBeInstanceOf(Exception::class);
});

it('is final class', function (): void {
    // Arrange
    $reflection = new ReflectionClass(ProductImageException::class);

    // Assert
    expect($reflection->isFinal())->toBeTrue();
});

it('has fixed error code 400', function (): void {
    // Arrange
    $exception = new ProductImageException('Any message');

    // Assert
    expect($exception->getCode())->toBe(400);
});

it('prepends error prefix to message', function (string $originalMessage): void {
    // Arrange & Act
    $exception = new ProductImageException($originalMessage);

    // Assert
    expect($exception->getMessage())->toBe("Ошибка получения изображений: {$originalMessage}");
})->with([
    'Invalid image format',
    'Image not found',
    'Service unavailable',
    'Timeout',
]);

it('can be caught as Exception', function (): void {
    // Arrange
    $caught = false;

    try {
        throw new ProductImageException('Test');
    } catch (Exception $e) {
        $caught = true;
    }

    // Assert
    expect($caught)->toBeTrue();
});

it('formats empty message correctly', function (): void {
    // Arrange & Act
    $exception = new ProductImageException('');

    // Assert
    expect($exception->getMessage())->toBe('Ошибка получения изображений: ');
});
