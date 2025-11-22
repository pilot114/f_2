<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Event;
use DateTimeImmutable;

it('create event', function (): void {
    $country = new Country(1, 'Россия');
    $start = new DateTimeImmutable('2024-01-01 10:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $event = new Event(
        id: 1,
        name: 'Тестовое мероприятие',
        country: $country,
        cityName: 'Москва',
        start: $start,
        end: $end
    );

    expect($event->id)->toBe(1);
});

it('converts to event response', function (): void {
    $country = new Country(1, 'Россия');
    $start = new DateTimeImmutable('2024-01-01 10:00:00');
    $end = new DateTimeImmutable('2024-01-01 18:00:00');

    $event = new Event(
        id: 1,
        name: 'Тестовое мероприятие',
        country: $country,
        cityName: 'Москва',
        start: $start,
        end: $end
    );

    $result = $event->toEventResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Тестовое мероприятие');
    expect($result->countryName)->toBe('Россия');
    expect($result->cityName)->toBe('Москва');
    expect($result->start)->toBe($start);
    expect($result->end)->toBe($end);
});
