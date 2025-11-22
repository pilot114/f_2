<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Exception;

use App\Common\Exception\DomainException;
use App\Common\Exception\InvariantDomainException;

it('creates invariant domain exception with message', function (): void {
    $exception = new InvariantDomainException('Test error');

    expect($exception->getMessage())->toBe('Test error');
});

it('has correct invariant code', function (): void {
    $exception = new InvariantDomainException('Test');

    expect($exception->getCode())->toBe(DomainException::INVARIANT)
        ->and($exception->getCode())->toBe(601);
});

it('has empty context by default', function (): void {
    $exception = new InvariantDomainException('Test');

    expect($exception->getContext())->toBeEmpty();
});

it('has correct domain constants', function (): void {
    expect(DomainException::INVARIANT)->toBe(601)
        ->and(DomainException::STATIC)->toBe(602)
        ->and(DomainException::FILE)->toBe(603);
});

it('is throwable', function (): void {
    expect(fn () => throw new InvariantDomainException('Test error'))
        ->toThrow(InvariantDomainException::class, 'Test error');
});

it('extends standard DomainException', function (): void {
    $exception = new InvariantDomainException('Test');

    expect($exception)->toBeInstanceOf(\DomainException::class)
        ->and($exception)->toBeInstanceOf(DomainException::class);
});

it('handles cyrillic in message', function (): void {
    $exception = new InvariantDomainException('Ошибка валидации данных');

    expect($exception->getMessage())->toBe('Ошибка валидации данных');
});
