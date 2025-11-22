<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\RewardTypeRequest;
use App\Domain\Events\Rewards\Repository\GroupQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetGroupsUseCase;
use Illuminate\Support\Collection;
use Mockery;

it('get group list', function (): void {
    $repository = Mockery::mock(GroupQueryRepository::class);
    $useCase = new GetGroupsUseCase($repository);

    $country = FilterOption::Q_ANY;
    $search = null;
    $status = false;
    $rewardTypes = [
        new RewardTypeRequest(id: 1, name: 'прочие'),
    ];

    $repository->shouldReceive('getGroups')->with($country, $search, $status, $rewardTypes)->andReturn(collect());

    $result = $useCase->getGroups($country, $search, $status, $rewardTypes);

    expect($result)->toBeInstanceOf(Collection::class);

})->with('group list');

it('get groups with integer country', function (): void {
    $repository = Mockery::mock(GroupQueryRepository::class);
    $useCase = new GetGroupsUseCase($repository);

    $country = 1;
    $search = 'test search';
    $status = true;
    $rewardTypes = [
        new RewardTypeRequest(1, 'Тип 1'),
        new RewardTypeRequest(2, 'Тип 2'),
    ];

    $repository->shouldReceive('getGroups')
        ->with($country, $search, $status, $rewardTypes)
        ->andReturn(collect());

    $result = $useCase->getGroups($country, $search, $status, $rewardTypes);

    expect($result)->toBeInstanceOf(Collection::class);
});

it('get groups with reward types including filter option', function (): void {
    $repository = Mockery::mock(GroupQueryRepository::class);
    $useCase = new GetGroupsUseCase($repository);

    $country = FilterOption::Q_SOME;
    $search = null;
    $status = false;
    $rewardTypes = [
        new RewardTypeRequest(1, 'Тип 1'),
        new RewardTypeRequest(FilterOption::Q_NONE, 'Без типа'),
    ];

    $repository->shouldReceive('getGroups')
        ->with($country, $search, $status, $rewardTypes)
        ->andReturn(collect());

    $result = $useCase->getGroups($country, $search, $status, $rewardTypes);

    expect($result)->toBeInstanceOf(Collection::class);
});

it('get groups with all filter options', function (): void {
    $repository = Mockery::mock(GroupQueryRepository::class);
    $useCase = new GetGroupsUseCase($repository);

    $country = FilterOption::Q_NONE;
    $search = 'search query';
    $status = true;
    $rewardTypes = [];

    $repository->shouldReceive('getGroups')
        ->with($country, $search, $status, $rewardTypes)
        ->andReturn(collect());

    $result = $useCase->getGroups($country, $search, $status, $rewardTypes);

    expect($result)->toBeInstanceOf(Collection::class);
});
