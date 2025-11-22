<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\Entity;

use App\Domain\Marketing\CustomerHistory\Entity\Country;
use App\Domain\Marketing\CustomerHistory\Entity\Employee;
use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use App\Domain\Marketing\CustomerHistory\Entity\Language;
use App\Domain\Marketing\CustomerHistory\Entity\State;
use DateTimeImmutable;

it('creates history item entity with all values', function (): void {
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
});

it('creates history item entity with nullable values', function (): void {
    $id = 1;

    $historyItem = new HistoryItem(
        $id,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        []
    );

    expect($historyItem->id)->toBe($id)
        ->and($historyItem->createDt)->toBeNull()
        ->and($historyItem->historyPreview)->toBeNull()
        ->and($historyItem->history)->toBeNull()
        ->and($historyItem->commentary)->toBeNull()
        ->and($historyItem->writeCountryName)->toBeNull()
        ->and($historyItem->writeCityName)->toBeNull()
        ->and($historyItem->lang)->toBeNull()
        ->and($historyItem->state)->toBeNull()
        ->and($historyItem->employee)->toBeNull()
        ->and($historyItem->countries)->toBe([]);
});

it('has correct table constant', function (): void {
    expect(HistoryItem::TABLE)->toBe('net.nc_story_of_customer');
});
