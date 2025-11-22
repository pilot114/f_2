<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\Language;
use App\Domain\Marketing\CustomerHistory\Repository\LanguageQueryRepository;
use App\Domain\Marketing\CustomerHistory\UseCase\GetLanguagesUseCase;
use Illuminate\Support\Collection;
use Mockery;

it('gets languages through use case', function (): void {
    $repository = Mockery::mock(LanguageQueryRepository::class);
    $useCase = new GetLanguagesUseCase($repository);

    $mockLang1 = new Language('ru', 'Русский');
    $mockLang2 = new Language('en', 'English');
    $mockLang3 = new Language('de', 'Deutsch');
    $mockCollection = new Collection([$mockLang1, $mockLang2, $mockLang3]);

    $repository->expects('getLanguages')
        ->withNoArgs()
        ->andReturns($mockCollection);

    $result = $useCase->getLanguages();

    expect($result)->toBe($mockCollection)
        ->and($result->count())->toBe(3)
        ->and($result->first()->id)->toBe('ru')
        ->and($result->first()->name)->toBe('Русский');
});

it('handles empty results from repository', function (): void {
    $repository = Mockery::mock(LanguageQueryRepository::class);
    $useCase = new GetLanguagesUseCase($repository);

    $emptyCollection = new Collection([]);

    $repository->expects('getLanguages')
        ->withNoArgs()
        ->andReturns($emptyCollection);

    $result = $useCase->getLanguages();

    expect($result->isEmpty())->toBeTrue()
        ->and($result->count())->toBe(0);
});

it('calls repository without parameters', function (): void {
    $repository = Mockery::mock(LanguageQueryRepository::class);
    $useCase = new GetLanguagesUseCase($repository);

    $mockCollection = new Collection([]);

    // Verify that getLanguages is called without any parameters
    $repository->expects('getLanguages')
        ->withNoArgs()
        ->andReturns($mockCollection);

    $result = $useCase->getLanguages();

    expect($result)->toBe($mockCollection);
});
