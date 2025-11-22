<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\BackgroundImage;
use App\Domain\Marketing\AdventCalendar\Repository\GetBackgroundImagesQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\GetBackgroundImagesUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(GetBackgroundImagesQueryRepository::class);
    $this->useCase = new GetBackgroundImagesUseCase($this->repository);
});

test('getData returns background images collection', function (): void {
    // Arrange
    $backgroundImages = new Collection([
        new BackgroundImage(id: 1, name: "name", url: 'bg1.jpg'),
        new BackgroundImage(id: 2, name: "name2", url: 'bg2.jpg'),
    ]);

    $this->repository->method('getListOfBackgroundImages')->willReturn($backgroundImages);

    // Act
    $result = $this->useCase->getData();

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->first()->id)->toBe(1)
        ->and($result->first()->url)->toBe('bg1.jpg');
});
