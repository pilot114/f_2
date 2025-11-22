<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Event;
use App\Domain\Events\Rewards\Repository\EventQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetEventsForVerificationFilterUseCase;
use DateTimeImmutable;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(EventQueryRepository::class);
    $this->useCase = new GetEventsForVerificationFilterUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('create group', function (): void {

    $events = collect();
    $events->add(
        new Event(
            id: 1,
            name: 'test',
            country: new Country(
                id: 1,
                name: 'test'
            ),
            cityName: 'test',
            start: new DateTimeImmutable('2024-10-10'),
            end: new DateTimeImmutable('2024-10-15')
        )
    );

    $this->repository->shouldReceive('getEventsForVerificationFilter')
        ->once()
        ->andReturn($events);

    $result = $this->useCase->getList();
    expect($result->first())->toBeInstanceOf(Event::class);
});
