<?php

declare(strict_types=1);

use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\UseCase\GetAvailableRewardTypesUseCase;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->readRewardTypes = Mockery::mock(QueryRepositoryInterface::class);
    $this->useCase = new GetAvailableRewardTypesUseCase($this->readRewardTypes);
});

afterEach(function (): void {
    Mockery::close();
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(GetAvailableRewardTypesUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('readRewardTypes');
});

it('has getAll method with correct signature', function (): void {
    $reflection = new ReflectionClass(GetAvailableRewardTypesUseCase::class);
    $method = $reflection->getMethod('getAll');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(0);
});

it('getAll method returns Enumerable', function (): void {
    $reflection = new ReflectionClass(GetAvailableRewardTypesUseCase::class);
    $method = $reflection->getMethod('getAll');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('Illuminate\Support\Enumerable');
});

it('has private readRewardTypes property', function (): void {
    $reflection = new ReflectionClass(GetAvailableRewardTypesUseCase::class);

    expect($reflection->hasProperty('readRewardTypes'))->toBeTrue();

    $property = $reflection->getProperty('readRewardTypes');
    expect($property->isPrivate())->toBeTrue();
});

it('returns all reward types from repository', function (): void {
    $rewardTypes = new Collection([
        new RewardType(id: 1, name: 'Badge'),
        new RewardType(id: 2, name: 'Certificate'),
    ]);

    $this->readRewardTypes
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($rewardTypes);

    $result = $this->useCase->getAll();

    expect($result)->toBe($rewardTypes)
        ->and($result->count())->toBe(2);
});

it('returns empty collection when no reward types exist', function (): void {
    $emptyCollection = new Collection([]);

    $this->readRewardTypes
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($emptyCollection);

    $result = $this->useCase->getAll();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});

it('delegates directly to repository without modification', function (): void {
    $rewardTypes = new Collection([
        new RewardType(id: 5, name: 'Trophy'),
    ]);

    $this->readRewardTypes
        ->shouldReceive('findAll')
        ->once()
        ->andReturn($rewardTypes);

    $result = $this->useCase->getAll();

    expect($result)->toBe($rewardTypes);
});
