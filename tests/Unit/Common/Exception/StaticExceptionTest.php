<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Exception;

use App\Common\Exception\DomainException;
use App\Common\Exception\StaticException;

it('creates StaticException with data', function (): void {
    // Arrange
    $data = [
        'name'    => 'ConnectionError',
        'message' => 'Failed to connect to static server',
    ];

    // Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->getMessage())->toBe('Ошибка работы с сервером статики: ConnectionError (Failed to connect to static server)')
        ->and($exception->getCode())->toBe(DomainException::STATIC)
        ->and($exception->data)->toBe($data);
});

it('extends DomainException', function (): void {
    // Arrange
    $exception = new StaticException([
        'name'    => 'Test',
        'message' => 'Error',
    ]);

    // Assert
    expect($exception)->toBeInstanceOf(DomainException::class);
});

it('has correct error code', function (): void {
    // Arrange
    $exception = new StaticException([
        'name'    => 'Test',
        'message' => 'Error',
    ]);

    // Assert
    expect($exception->getCode())->toBe(602);
});

it('handles missing name in data', function (): void {
    // Arrange
    $data = [
        'message' => 'Some error occurred',
    ];

    // Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->getMessage())->toBe('Ошибка работы с сервером статики: Имя не задано (Some error occurred)');
});

it('handles missing message in data', function (): void {
    // Arrange
    $data = [
        'name' => 'ErrorName',
    ];

    // Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->getMessage())->toBe('Ошибка работы с сервером статики: ErrorName (Неизвестная ошибка)');
});

it('handles empty data array', function (): void {
    // Arrange
    $data = [];

    // Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->getMessage())->toBe('Ошибка работы с сервером статики: Имя не задано (Неизвестная ошибка)')
        ->and($exception->data)->toBe([]);
});

it('stores original data', function (): void {
    // Arrange
    $data = [
        'name'    => 'FileNotFound',
        'message' => 'File does not exist',
        'extra'   => 'additional info',
    ];

    // Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->data)->toBe($data)
        ->and($exception->data['extra'])->toBe('additional info');
});

it('can be caught as DomainException', function (): void {
    // Arrange
    $caught = false;

    try {
        throw new StaticException([
            'name'    => 'Test',
            'message' => 'Error',
        ]);
    } catch (DomainException $e) {
        $caught = true;
    }

    // Assert
    expect($caught)->toBeTrue();
});

it('formats message with different error types', function (array $data, string $expectedMessage): void {
    // Arrange & Act
    $exception = new StaticException($data);

    // Assert
    expect($exception->getMessage())->toBe($expectedMessage);
})->with([
    [
        [
            'name'    => 'Timeout',
            'message' => 'Request timeout',
        ],
        'Ошибка работы с сервером статики: Timeout (Request timeout)',
    ],
    [
        [
            'name'    => 'AuthError',
            'message' => 'Unauthorized',
        ],
        'Ошибка работы с сервером статики: AuthError (Unauthorized)',
    ],
    [
        [
            'name'    => 'NotFound',
            'message' => 'Resource not found',
        ],
        'Ошибка работы с сервером статики: NotFound (Resource not found)',
    ],
]);

it('has empty context by default', function (): void {
    // Arrange
    $exception = new StaticException([
        'name'    => 'Test',
        'message' => 'Error',
    ]);

    // Assert
    expect($exception->getContext())->toBe([]);
});
