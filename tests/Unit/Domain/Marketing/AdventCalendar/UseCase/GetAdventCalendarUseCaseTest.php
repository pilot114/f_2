<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use App\Domain\Marketing\AdventCalendar\Entity\MonthParams;
use App\Domain\Marketing\AdventCalendar\Entity\MonthProduct;
use App\Domain\Marketing\AdventCalendar\Repository\GetAdventCalendarQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\GetAdventCalendarUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = mock(GetAdventCalendarQueryRepository::class);

    $this->useCase = new GetAdventCalendarUseCase(
        $this->repository,
    );
});

test('getData returns mapped advent items with product images', function (): void {
    // Arrange
    $products = [
        new MonthProduct(id: 1, code: 'prod1', name: 'name'),
        new MonthProduct(id: 2, code: 'prod2', name: 'name2'),
    ];
    $adventItem = new AdventItem(id: 1, params: new MonthParams(year: 2025, month: 12, name: "Декабрь"), calendarId: 1, products: $products, offers: []);
    $adventCollection = new Collection([$adventItem]);

    $this->repository->allows('getData')->andReturn($adventCollection);
    // Act
    $result = $this->useCase->getData('shop123');

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first()->products[0]->code)->toBe('prod1')
        ->and($result->first()->products[1]->code)->toBe('prod2');
});

test('getData handles empty product collection', function (): void {
    // Arrange
    $adventCollection = new Collection([]);

    $this->repository->allows('getData')->zeroOrMoreTimes()->andReturn($adventCollection);
    $result = $this->useCase->getData(null);

    // Assert
    expect($result)->toBeEmpty();
});
