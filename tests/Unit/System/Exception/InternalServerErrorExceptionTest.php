<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Exception;

use App\System\Exception\InternalServerErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('creates exception with default message', function (): void {
    $exception = new InternalServerErrorException();

    expect($exception)->toBeInstanceOf(HttpException::class)
        ->and($exception->getStatusCode())->toBe(500)
        ->and($exception->getMessage())->toBe('Внутренняя ошибка сервера');
});

it('creates exception with custom message', function (): void {
    $exception = new InternalServerErrorException('Custom error message');

    expect($exception->getStatusCode())->toBe(500)
        ->and($exception->getMessage())->toBe('Custom error message');
});

it('has correct status code', function (): void {
    $exception = new InternalServerErrorException();

    expect($exception->getStatusCode())->toBe(500);
});

it('is throwable', function (): void {
    expect(fn () => throw new InternalServerErrorException('Test error'))
        ->toThrow(InternalServerErrorException::class, 'Test error');
});

it('extends http exception', function (): void {
    $exception = new InternalServerErrorException();

    expect($exception)->toBeInstanceOf(HttpException::class);
});

it('handles long error messages', function (): void {
    $longMessage = 'Error: ' . str_repeat('detailed description ', 50);
    $exception = new InternalServerErrorException($longMessage);

    expect($exception->getMessage())->toBe($longMessage);
});

it('handles empty custom message', function (): void {
    $exception = new InternalServerErrorException('');

    expect($exception->getMessage())->toBe('')
        ->and($exception->getStatusCode())->toBe(500);
});

it('handles cyrillic in custom message', function (): void {
    $exception = new InternalServerErrorException('Произошла критическая ошибка системы');

    expect($exception->getMessage())->toBe('Произошла критическая ошибка системы');
});
