<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\DTO\CreateMonthRequest;
use App\Domain\Marketing\AdventCalendar\DTO\SaveMonthParamsRequest;
use App\Domain\Marketing\AdventCalendar\Entity\Language;
use App\Domain\Marketing\AdventCalendar\Repository\WriteMonthCommandRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveMonthParamsUseCase;
use Database\Connection\TransactionInterface;

beforeEach(function (): void {
    $this->repository = $this->createMock(WriteMonthCommandRepository::class);
    $this->transaction = $this->createMock(TransactionInterface::class);
    $this->useCase = new SaveMonthParamsUseCase($this->repository, $this->transaction);
});

test('createMonth adds period to calendar', function (): void {
    // Arrange
    $request = new CreateMonthRequest(year: 2025, month: 12, shop: 'shop123');
    $expectedId = 456;

    $this->repository
        ->expects($this->once())
        ->method('addPeriodToCalendar')
        ->with(2025, 12, 'shop123')
        ->willReturn($expectedId);

    // Act
    $result = $this->useCase->createMonth($request);

    // Assert
    expect($result)->toBe($expectedId);
});

test('saveMonthParams updates languages in transaction', function (): void {
    // Arrange
    $lang1 = new Language(lang: 'en', label: 'Dec', title: 'December');
    $lang2 = new Language(lang: 'ru', label: 'Дек', title: 'Декабрь');
    $request = new SaveMonthParamsRequest(
        calendarId: 123,
        langs: [$lang1, $lang2]
    );

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repository
        ->expects($this->exactly(2))
        ->method('updatePeriodLang');

    $this->transaction->expects($this->once())->method('commit');

    // Act
    $result = $this->useCase->saveMonthParams($request);

    // Assert
    expect($result)->toBeTrue();
});
