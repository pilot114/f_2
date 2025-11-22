<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\Shop;
use App\Domain\Marketing\AdventCalendar\Repository\GetShopsQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\GetShopsUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(GetShopsQueryRepository::class);
    $this->useCase = new GetShopsUseCase($this->repository);
});

test('getData returns shops with language filter', function (): void {
    // Arrange
    $lang = 'en';
    $shops = new Collection([
        new Shop(code: $lang, name: 'Shop 1', nameRu: "Магазин"),
        new Shop(code: $lang, name: 'Shop 2', nameRu: "Магазин"),
    ]);

    $this->repository
        ->expects($this->once())
        ->method('getListOfShops')
        ->with($lang)
        ->willReturn($shops);

    // Act
    $result = $this->useCase->getData($lang);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->first()->code)->toBe("en")
        ->and($result->first()->name)->toBe('Shop 1');
});

test('getData works with null language', function (): void {
    // Arrange
    $shops = new Collection([]);
    $this->repository
        ->expects($this->once())
        ->method('getListOfShops')
        ->with(null)
        ->willReturn($shops);

    // Act
    $result = $this->useCase->getData(null);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});
