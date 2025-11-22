<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Rang;
use DateTimeImmutable;

it('creates rang', function (): void {
    $date = new DateTimeImmutable('2024-01-01');

    $rang = new Rang(
        id: 1,
        rang: 'BR1',
        name: 'Бронзовый',
        date: $date
    );

    $result = $rang->toRangResponse();

    expect($result->id)->toBe(1);
    expect($result->rang)->toBe('BR1');
    expect($result->name)->toBe('Бронзовый');
    expect($result->date)->toBe($date->format(DateTimeImmutable::ATOM));
});

it('converts to rang response', function (): void {
    $date = new DateTimeImmutable('2024-01-01 12:00:00');

    $rang = new Rang(
        id: 5,
        rang: 'GLD',
        name: 'Золотой',
        date: $date
    );

    $result = $rang->toRangResponse();

    expect($result->id)->toBe(5);
    expect($result->rang)->toBe('GLD');
    expect($result->name)->toBe('Золотой');
    expect($result->date)->toBe($date->format(DateTimeImmutable::ATOM));
});
