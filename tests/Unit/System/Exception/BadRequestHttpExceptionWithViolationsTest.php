<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Exception;

use App\System\Exception\BadRequestHttpExceptionWithViolations;
use Mockery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

it('creates exception with violations', function (): void {
    $violations = new ConstraintViolationList();
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    expect($exception)->toBeInstanceOf(BadRequestHttpException::class)
        ->and($exception->getMessage())->toBe('Невалидные данные')
        ->and($exception->getStatusCode())->toBe(400);
});

it('returns empty array when no violations', function (): void {
    $violations = new ConstraintViolationList();
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    expect($exception->getViolations())->toBeEmpty();
});

it('returns violations as array', function (): void {
    $violation1 = new ConstraintViolation(
        'Field is required',
        '',
        [],
        null,
        'email',
        null
    );

    $violation2 = new ConstraintViolation(
        'Must be at least 3 characters',
        '',
        [],
        null,
        'name',
        null
    );

    $violations = new ConstraintViolationList([$violation1, $violation2]);
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    $result = $exception->getViolations();

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBe([
            'field'   => 'email',
            'message' => 'Field is required',
        ])
        ->and($result[1])->toBe([
            'field'   => 'name',
            'message' => 'Must be at least 3 characters',
        ]);
});

it('handles single violation', function (): void {
    $violation = new ConstraintViolation(
        'Invalid email format',
        '',
        [],
        null,
        'email',
        null
    );

    $violations = new ConstraintViolationList([$violation]);
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    $result = $exception->getViolations();

    expect($result)->toHaveCount(1)
        ->and($result[0]['field'])->toBe('email')
        ->and($result[0]['message'])->toBe('Invalid email format');
});

it('handles cyrillic error messages', function (): void {
    $violation = new ConstraintViolation(
        'Поле обязательно для заполнения',
        '',
        [],
        null,
        'username',
        null
    );

    $violations = new ConstraintViolationList([$violation]);
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    $result = $exception->getViolations();

    expect($result[0]['message'])->toBe('Поле обязательно для заполнения');
});

it('handles nested property paths', function (): void {
    $violation = new ConstraintViolation(
        'Invalid value',
        '',
        [],
        null,
        'user.profile.email',
        null
    );

    $violations = new ConstraintViolationList([$violation]);
    $exception = new BadRequestHttpExceptionWithViolations($violations);

    $result = $exception->getViolations();

    expect($result[0]['field'])->toBe('user.profile.email');
});

it('is throwable', function (): void {
    $violations = new ConstraintViolationList();

    expect(fn () => throw new BadRequestHttpExceptionWithViolations($violations))
        ->toThrow(BadRequestHttpExceptionWithViolations::class);
});

afterEach(function (): void {
    Mockery::close();
});
