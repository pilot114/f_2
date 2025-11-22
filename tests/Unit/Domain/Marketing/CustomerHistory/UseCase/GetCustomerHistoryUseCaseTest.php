<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\Country;
use App\Domain\Marketing\CustomerHistory\Entity\Employee;
use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use App\Domain\Marketing\CustomerHistory\Entity\Language;
use App\Domain\Marketing\CustomerHistory\Entity\State;
use App\Domain\Marketing\CustomerHistory\Enum\Status;
use App\Domain\Marketing\CustomerHistory\Repository\CustomerHistoryQueryRepository;
use App\Domain\Marketing\CustomerHistory\UseCase\GetCustomerHistoryUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

it('gets customer history data through use case', function (): void {
    $repository = Mockery::mock(CustomerHistoryQueryRepository::class);
    $useCase = new GetCustomerHistoryUseCase($repository);

    $id = 1;
    $createDt = new DateTimeImmutable('2023-06-15 10:30:00');
    $historyPreview = 'Отличный клиент';
    $history = 'Полная история клиента...';
    $commentary = 'Комментарий модератора';
    $writeCountryName = 'Россия';
    $writeCityName = 'Москва';

    $language = new Language('ru', 'Русский');
    $state = new State(1, 'На модерации');
    $employee = new Employee(1, 'Иванов Иван Иванович');
    $countries = [
        new Country('ru', 'Россия'),
    ];

    $historyItem = new HistoryItem(
        $id,
        $createDt,
        $historyPreview,
        $history,
        $commentary,
        $writeCountryName,
        $writeCityName,
        $language,
        $state,
        $employee,
        $countries
    );

    expect($historyItem->id)->toBe($id)
        ->and($historyItem->createDt)->toBe($createDt)
        ->and($historyItem->historyPreview)->toBe($historyPreview)
        ->and($historyItem->history)->toBe($history)
        ->and($historyItem->commentary)->toBe($commentary)
        ->and($historyItem->writeCountryName)->toBe($writeCountryName)
        ->and($historyItem->writeCityName)->toBe($writeCityName)
        ->and($historyItem->lang)->toBe($language)
        ->and($historyItem->state)->toBe($state)
        ->and($historyItem->employee)->toBe($employee)
        ->and($historyItem->countries)->toBe($countries);

    $mockCollection = new Collection([$historyItem]);

    $q = 'search term';
    $state = Status::MODERATION;
    $lang = 'ru';
    $dateFrom = new DateTimeImmutable('2023-01-01');
    $dateTill = new DateTimeImmutable('2023-12-31');
    $page = 1;
    $perPage = 1;

    $repository->expects('getData')
        ->with($q, $state, $lang, $dateFrom, $dateTill, $page, $perPage)
        ->andReturns($mockCollection);

    $result = $useCase->getData($q, $state, $lang, $dateFrom, $dateTill, $page, $perPage);

    expect($result)->toBe($mockCollection)
        ->and($result->count())->toBe(1)
        ->and($result->first()->id)->toBe(1);
});

it('passes null parameters correctly to repository', function (): void {
    $repository = Mockery::mock(CustomerHistoryQueryRepository::class);
    $useCase = new GetCustomerHistoryUseCase($repository);

    $mockCollection = new Collection([]);

    $repository->expects('getData')
        ->with(null, null, null, null, null, 1, 1)
        ->andReturns($mockCollection);

    $result = $useCase->getData(null, null, null, null, null, 1, 1);

    expect($result)->toBe($mockCollection)
        ->and($result->isEmpty())->toBeTrue();
});

it('handles empty results from repository', function (): void {
    $repository = Mockery::mock(CustomerHistoryQueryRepository::class);
    $useCase = new GetCustomerHistoryUseCase($repository);

    $emptyCollection = new Collection([]);

    $repository->expects('getData')
        ->andReturns($emptyCollection);

    $result = $useCase->getData('nonexistent', Status::MODERATION, 'unknown', null, null, 1, 1);

    expect($result->isEmpty())->toBeTrue()
        ->and($result->count())->toBe(0);
});
