<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Repository\GetAdventProductQueryRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\SearchProductOfMonthUseCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->repository = Mockery::mock(GetAdventProductQueryRepository::class);
    $this->useCase = new SearchProductOfMonthUseCase($this->repository);
});

it('is readonly class', function (): void {
    $reflection = new ReflectionClass(SearchProductOfMonthUseCase::class);

    expect($reflection->isReadOnly())->toBeTrue();
});

it('retrieves data from repository', function (): void {
    $countryId = 'RU';
    $query = 'product';

    $expectedData = new Collection([
        [
            'id'   => 1,
            'name' => 'Product 1',
        ],
        [
            'id'   => 2,
            'name' => 'Product 2',
        ],
    ]);

    $this->repository
        ->shouldReceive('getData')
        ->with($countryId, $query)
        ->andReturn($expectedData);

    $result = $this->useCase->getData($countryId, $query);

    expect($result)->toBeInstanceOf(Enumerable::class)
        ->and($result->count())->toBe(2);
});

it('handles null query parameter', function (): void {
    $countryId = 'RU';

    $this->repository
        ->shouldReceive('getData')
        ->with($countryId, null)
        ->andReturn(new Collection([]));

    $result = $this->useCase->getData($countryId, null);

    expect($result)->toBeInstanceOf(Enumerable::class);
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(SearchProductOfMonthUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('repository');
});

it('has getData method with correct signature', function (): void {
    $reflection = new ReflectionClass(SearchProductOfMonthUseCase::class);
    $method = $reflection->getMethod('getData');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('countryId')
        ->and($parameters[1]->getName())->toBe('q');
});
