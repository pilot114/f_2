<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\WorkTime;
use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use DateTimeImmutable;

it('creates work time with all fields', function (): void {
    $start = new DateTimeImmutable('2024-01-01 09:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 10,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::MOSCOW,
    );

    expect($workTime->getId())->toBe(1)
        ->and($workTime->userId)->toBe(10)
        ->and($workTime->getStart())->toBe($start)
        ->and($workTime->getEnd())->toBe($end)
        ->and($workTime->getTimeZone())->toBe(WorkTimeTimeZone::MOSCOW);
});

it('converts to array', function (): void {
    $start = new DateTimeImmutable('2024-02-15 08:00:00');
    $end = new DateTimeImmutable('2024-02-15 17:00:00');

    $workTime = new WorkTime(
        id: 5,
        userId: 20,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::NOVOSIBIRSK,
    );

    $result = $workTime->toArray();

    expect($result)->toHaveKeys(['start', 'end', 'timeZone'])
        ->and($result['start'])->toBe($start->format(DateTimeImmutable::ATOM))
        ->and($result['end'])->toBe($end->format(DateTimeImmutable::ATOM))
        ->and($result['timeZone'])->toBe(WorkTimeTimeZone::NOVOSIBIRSK);
});

it('updates work time', function (): void {
    $oldStart = new DateTimeImmutable('2024-01-01 09:00:00');
    $oldEnd = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 10,
        start: $oldStart,
        end: $oldEnd,
        timeZone: WorkTimeTimeZone::MOSCOW,
    );

    $newStart = new DateTimeImmutable('2024-01-01 10:00:00');
    $newEnd = new DateTimeImmutable('2024-01-01 19:00:00');

    $workTime->updateTime($newStart, $newEnd, WorkTimeTimeZone::NOVOSIBIRSK);

    expect($workTime->getStart())->toBe($newStart)
        ->and($workTime->getEnd())->toBe($newEnd)
        ->and($workTime->getTimeZone())->toBe(WorkTimeTimeZone::NOVOSIBIRSK);
});

it('handles moscow timezone', function (): void {
    $start = new DateTimeImmutable('2024-01-01 09:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 1,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::MOSCOW,
    );

    expect($workTime->getTimeZone())->toBe(WorkTimeTimeZone::MOSCOW)
        ->and($workTime->getTimeZone()->value)->toBe('Europe/Moscow');
});

it('handles novosibirsk timezone', function (): void {
    $start = new DateTimeImmutable('2024-01-01 09:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 1,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::NOVOSIBIRSK,
    );

    expect($workTime->getTimeZone())->toBe(WorkTimeTimeZone::NOVOSIBIRSK)
        ->and($workTime->getTimeZone()->value)->toBe('Asia/Novosibirsk');
});

it('userId is readonly', function (): void {
    $start = new DateTimeImmutable('2024-01-01 09:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 42,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::MOSCOW,
    );

    expect($workTime->userId)->toBe(42);
});

it('toArray does not include id or userId', function (): void {
    $start = new DateTimeImmutable('2024-01-01 09:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $workTime = new WorkTime(
        id: 1,
        userId: 10,
        start: $start,
        end: $end,
        timeZone: WorkTimeTimeZone::MOSCOW,
    );

    $result = $workTime->toArray();

    expect($result)->not->toHaveKey('id')
        ->and($result)->not->toHaveKey('userId')
        ->and($result)->toHaveCount(3);
});
