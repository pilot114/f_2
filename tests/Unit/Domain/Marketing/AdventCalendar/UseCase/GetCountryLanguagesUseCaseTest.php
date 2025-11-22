<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\CountryLanguage;
use App\Domain\Marketing\AdventCalendar\Repository\GetCountryLanguagesQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\GetCountryLanguagesUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(GetCountryLanguagesQueryRepository::class);
    $this->useCase = new GetCountryLanguagesUseCase($this->repository);
});

test('getData returns languages for specific country', function (): void {
    // Arrange
    $countryId = 'RU';
    $languages = new Collection([
        new CountryLanguage(lang: 'ru', isMain: true, name: 'Russian'),
        new CountryLanguage(lang: 'en', isMain: false, name: 'English'),
    ]);

    $this->repository
        ->expects($this->once())
        ->method('getLanguagesOfCountry')
        ->with($countryId)
        ->willReturn($languages);

    // Act
    $result = $this->useCase->getData($countryId);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->first()->lang)->toBe('ru')
        ->and($result->first()->name)->toBe('Russian');
});
