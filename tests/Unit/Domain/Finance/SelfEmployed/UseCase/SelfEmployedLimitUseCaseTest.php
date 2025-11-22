<?php

declare(strict_types=1);

use App\Domain\Finance\SelfEmployed\Repository\SelfEmployedLimitRepository;
use App\Domain\Finance\SelfEmployed\UseCase\SelfEmployedLimitUseCase;

beforeEach(function (): void {
    $this->repository = Mockery::mock(SelfEmployedLimitRepository::class);
    $this->useCase = new SelfEmployedLimitUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('gets report from repository', function (): void {
    $dateFrom = new DateTimeImmutable('2024-01-01');
    $dateTill = new DateTimeImmutable('2024-12-31');

    $expectedData = [
        [
            'id'     => 1,
            'name'   => 'Test 1',
            'amount' => 1000,
        ],
        [
            'id'     => 2,
            'name'   => 'Test 2',
            'amount' => 2000,
        ],
    ];

    $this->repository->shouldReceive('getReport')
        ->once()
        ->with($dateFrom, $dateTill)
        ->andReturn($expectedData);

    $result = $this->useCase->getReport($dateFrom, $dateTill);

    expect($result)->toBe($expectedData)
        ->and($result)->toHaveCount(2);
});

it('passes dates correctly to repository', function (): void {
    $dateFrom = new DateTimeImmutable('2023-06-15');
    $dateTill = new DateTimeImmutable('2023-12-31');

    $this->repository->shouldReceive('getReport')
        ->once()
        ->with(
            Mockery::on(fn ($d): bool => $d->format('Y-m-d') === '2023-06-15'),
            Mockery::on(fn ($d): bool => $d->format('Y-m-d') === '2023-12-31')
        )
        ->andReturn([]);

    $this->useCase->getReport($dateFrom, $dateTill);
});

it('returns empty array when no data', function (): void {
    $dateFrom = new DateTimeImmutable('2024-01-01');
    $dateTill = new DateTimeImmutable('2024-01-31');

    $this->repository->shouldReceive('getReport')
        ->once()
        ->andReturn([]);

    $result = $this->useCase->getReport($dateFrom, $dateTill);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});
