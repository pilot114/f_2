<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use App\Domain\Marketing\AdventCalendar\Repository\GetOfferQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\GetOfferUseCase;

beforeEach(function (): void {
    $this->repository = mock(GetOfferQueryRepository::class);
    $this->useCase = new GetOfferUseCase($this->repository);
});

test('getData returns offer when exists', function (): void {
    // Arrange
    $offerId = 123;
    $offer = new Offer(backgroundImageId: 1, langs: [], id: $offerId);

    $this->repository
        ->allows('getData')
        ->once()
        ->with($offerId)
        ->andReturns($offer);

    // Act
    $result = $this->useCase->getData($offerId);

    // Assert
    expect($result)->toBeInstanceOf(Offer::class)
        ->and($result->id)->toBe($offerId);
});

test('getData returns exception', function (): void {
    // Arrange
    $offerId = 999;
    $this->repository
        ->allows('getData')
        ->once()
        ->with($offerId)
        ->andReturn(null);

    $this->expectExceptionMessage('Предложение 999 не найдено');

    $this->useCase->getData($offerId);

});
