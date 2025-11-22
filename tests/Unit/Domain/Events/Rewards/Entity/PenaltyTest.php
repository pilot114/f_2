<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Penalty;
use DateTimeImmutable;

it('creates penalty', function (): void {
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('2024-12-31');

    $penalty = new Penalty(
        id: 1,
        name: 'Штраф за нарушение',
        prim: 'Описание нарушения',
        start: $start,
        end: $end
    );

    expect($penalty->id)->toBe(1);
    expect($penalty->name)->toBe('Штраф за нарушение');
    expect($penalty->prim)->toBe('Описание нарушения');
});

it('creates penalty without end date', function (): void {
    $start = new DateTimeImmutable('2024-01-01');

    $penalty = new Penalty(
        id: 1,
        name: 'Штраф за нарушение',
        prim: 'Описание нарушения',
        start: $start
    );

    $result = $penalty->toPenaltyResponse();

    expect($result->end)->toBeNull();
});

it('converts to penalty response', function (): void {
    $start = new DateTimeImmutable('2024-01-01 10:00:00');
    $end = new DateTimeImmutable('2024-12-31 23:59:59');

    $penalty = new Penalty(
        id: 1,
        name: 'Штраф за нарушение',
        prim: 'Описание нарушения',
        start: $start,
        end: $end
    );

    $result = $penalty->toPenaltyResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Штраф за нарушение');
    expect($result->start)->toBe($start->format(DateTimeImmutable::ATOM));
    expect($result->end)->toBe($end->format(DateTimeImmutable::ATOM));
    expect($result->prim)->toBe('Описание нарушения');
});

it('converts to penalty response with null end', function (): void {
    $start = new DateTimeImmutable('2024-01-01');

    $penalty = new Penalty(
        id: 2,
        name: 'Постоянный штраф',
        prim: 'Без срока окончания',
        start: $start
    );

    $result = $penalty->toPenaltyResponse();

    expect($result->id)->toBe(2);
    expect($result->name)->toBe('Постоянный штраф');
    expect($result->start)->toBe($start->format(DateTimeImmutable::ATOM));
    expect($result->end)->toBeNull();
    expect($result->prim)->toBe('Без срока окончания');
});
