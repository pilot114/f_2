<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Message\ExportReportMessage;

it('creates message with required fields', function (): void {
    $reportId = 123;
    $input = [
        'ds' => '01.10.2025',
        'de' => '01.11.2025',
    ];
    $userId = 456;
    $userEmail = 'test@example.com';

    $message = new ExportReportMessage(
        reportId: $reportId,
        input: $input,
        userId: $userId,
        userEmail: $userEmail
    );

    expect($message->reportId)->toBe($reportId)
        ->and($message->input)->toBe($input)
        ->and($message->userId)->toBe($userId)
        ->and($message->userEmail)->toBe($userEmail);
});

it('creates message with empty input', function (): void {
    $reportId = 123;
    $input = [];
    $userId = 456;
    $userEmail = 'test@example.com';

    $message = new ExportReportMessage(
        reportId: $reportId,
        input: $input,
        userId: $userId,
        userEmail: $userEmail
    );

    expect($message->reportId)->toBe($reportId)
        ->and($message->input)->toBeEmpty()
        ->and($message->userId)->toBe($userId)
        ->and($message->userEmail)->toBe($userEmail);
});

it('preserves input data structure', function (): void {
    $input = [
        'ds'     => '01.10.2025',
        'de'     => '01.11.2025',
        'status' => 'active',
        'ids'    => [1, 2, 3],
    ];

    $message = new ExportReportMessage(
        reportId: 1,
        input: $input,
        userId: 1,
        userEmail: 'test@example.com'
    );

    expect($message->input)->toBe($input)
        ->and($message->input['ds'])->toBe('01.10.2025')
        ->and($message->input['de'])->toBe('01.11.2025')
        ->and($message->input['status'])->toBe('active')
        ->and($message->input['ids'])->toBe([1, 2, 3]);
});

it('is readonly', function (): void {
    $message = new ExportReportMessage(
        reportId: 1,
        input: [],
        userId: 1,
        userEmail: 'test@example.com'
    );

    $reflection = new ReflectionClass($message);

    expect($reflection->isReadOnly())->toBeTrue();
});
