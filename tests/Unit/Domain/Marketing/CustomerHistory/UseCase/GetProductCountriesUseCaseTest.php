<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\ProductCountry;
use App\Domain\Marketing\CustomerHistory\Repository\ProductCountryQueryRepository;
use App\Domain\Marketing\CustomerHistory\UseCase\GetProductCountriesUseCase;
use Illuminate\Support\Collection;
use Mockery;

it('gets product countries through use case', function (): void {
    $repository = Mockery::mock(ProductCountryQueryRepository::class);
    $useCase = new GetProductCountriesUseCase($repository);

    $mockCountry1 = new ProductCountry('RU', 'Россия');
    $mockCountry2 = new ProductCountry('BY', 'Беларусь');

    $mockCollection = new Collection([$mockCountry1, $mockCountry2]);

    $lang = 'ru';

    $repository->expects('getProductCountries')
        ->with($lang)
        ->andReturns($mockCollection);

    $result = $useCase->getProductCountries($lang);

    expect($result)->toBe($mockCollection)
        ->and($result->count())->toBe(2)
        ->and($result->first()->id)->toBe('RU')
        ->and($result->first()->name)->toBe('Россия');
});

it('handles empty results from repository', function (): void {
    $repository = Mockery::mock(ProductCountryQueryRepository::class);
    $useCase = new GetProductCountriesUseCase($repository);

    $emptyCollection = new Collection([]);

    $repository->expects('getProductCountries')
        ->with('nonexistent')
        ->andReturns($emptyCollection);

    $result = $useCase->getProductCountries('nonexistent');

    expect($result->isEmpty())->toBeTrue()
        ->and($result->count())->toBe(0);
});

it('passes language parameter correctly', function (): void {
    $repository = Mockery::mock(ProductCountryQueryRepository::class);
    $useCase = new GetProductCountriesUseCase($repository);

    $mockCollection = new Collection([]);

    $testLang = 'en';

    $repository->expects('getProductCountries')
        ->with($testLang)
        ->andReturns($mockCollection);

    $result = $useCase->getProductCountries($testLang);

    expect($result)->toBe($mockCollection);
});
