<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\RewardTypeRequest;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Repository\GroupQueryRepository;
use Database\Connection\ReadDatabaseInterface;
use Generator;
use Mockery;

it('get group list', function (array $params, array $rawDataFromDb): void {

    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new GroupQueryRepository($connection, getDataMapper());

    $generator = function () use ($rawDataFromDb): Generator {
        foreach ($rawDataFromDb as $item) {
            yield $item;
        }
    };
    $connection->shouldReceive('enableLazyLoading')->times(count($params));
    $connection->shouldReceive('query')->times(count($params))->andReturnUsing($generator);

    foreach ($params as $param) {
        $groups = $repository->getGroups($param['country'], $param['search'], $param['status'], []);
        expect($groups->first())->toBeInstanceOf(Group::class);
    }

})->with('group list');

it('filters groups by reward types with integer ids', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new GroupQueryRepository($connection, getDataMapper());

    $rawData = [
        [
            'id'                                                 => "1",
            'name'                                               => "Группа программ - тест",
            'programs_id'                                        => "9319499",
            'programs_name'                                      => "Летнее промо для США",
            'programs_nominations_id'                            => "9394261",
            'programs_nominations_name'                          => "Летнее промо для США 50$",
            'programs_nominations_rewards_id'                    => "10283054",
            'programs_nominations_rewards_name'                  => "Денежная премия",
            'programs_nominations_rewards_product_id'            => 1,
            'programs_nominations_rewards_commentary'            => null,
            'programs_nominations_rewards_statuses_id'           => "14",
            'programs_nominations_rewards_statuses_status'       => "1",
            'programs_nominations_rewards_statuses_country_id'   => "1",
            'programs_nominations_rewards_statuses_country_name' => "Россия",
        ],
    ];

    $generator = function () use ($rawData): Generator {
        foreach ($rawData as $item) {
            yield $item;
        }
    };

    $connection->shouldReceive('enableLazyLoading')->once();
    $connection->shouldReceive('query')->once()->andReturnUsing($generator);

    $rewardTypes = [
        new RewardTypeRequest(1, 'Тип 1'),
        new RewardTypeRequest(2, 'Тип 2'),
    ];

    $groups = $repository->getGroups(FilterOption::Q_ANY, null, false, $rewardTypes);
    expect($groups->first())->toBeInstanceOf(Group::class);
});

it('filters groups by reward types with filter option Q_NONE', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new GroupQueryRepository($connection, getDataMapper());

    $rawData = [
        [
            'id'                                                 => "1",
            'name'                                               => "Группа программ - тест",
            'programs_id'                                        => "9319499",
            'programs_name'                                      => "Летнее промо для США",
            'programs_nominations_id'                            => "9394261",
            'programs_nominations_name'                          => "Летнее промо для США 50$",
            'programs_nominations_rewards_id'                    => "10283054",
            'programs_nominations_rewards_name'                  => "Денежная премия",
            'programs_nominations_rewards_product_id'            => 1,
            'programs_nominations_rewards_commentary'            => null,
            'programs_nominations_rewards_statuses_id'           => "14",
            'programs_nominations_rewards_statuses_status'       => "1",
            'programs_nominations_rewards_statuses_country_id'   => "1",
            'programs_nominations_rewards_statuses_country_name' => "Россия",
        ],
    ];

    $generator = function () use ($rawData): Generator {
        foreach ($rawData as $item) {
            yield $item;
        }
    };

    $connection->shouldReceive('enableLazyLoading')->once();
    $connection->shouldReceive('query')->once()->andReturnUsing($generator);

    $rewardTypes = [
        new RewardTypeRequest(FilterOption::Q_NONE, 'Без типа'),
    ];

    $groups = $repository->getGroups(FilterOption::Q_ANY, null, false, $rewardTypes);
    expect($groups->first())->toBeInstanceOf(Group::class);
});

it('filters groups by reward types with mixed ids and filter options', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new GroupQueryRepository($connection, getDataMapper());

    $rawData = [
        [
            'id'                                                 => "1",
            'name'                                               => "Группа программ - тест",
            'programs_id'                                        => "9319499",
            'programs_name'                                      => "Летнее промо для США",
            'programs_nominations_id'                            => "9394261",
            'programs_nominations_name'                          => "Летнее промо для США 50$",
            'programs_nominations_rewards_id'                    => "10283054",
            'programs_nominations_rewards_name'                  => "Денежная премия",
            'programs_nominations_rewards_product_id'            => 1,
            'programs_nominations_rewards_commentary'            => null,
            'programs_nominations_rewards_statuses_id'           => "14",
            'programs_nominations_rewards_statuses_status'       => "1",
            'programs_nominations_rewards_statuses_country_id'   => "1",
            'programs_nominations_rewards_statuses_country_name' => "Россия",
        ],
    ];

    $generator = function () use ($rawData): Generator {
        foreach ($rawData as $item) {
            yield $item;
        }
    };

    $connection->shouldReceive('enableLazyLoading')->once();
    $connection->shouldReceive('query')->once()->andReturnUsing($generator);

    $rewardTypes = [
        new RewardTypeRequest(1, 'Тип 1'),
        new RewardTypeRequest(FilterOption::Q_NONE, 'Без типа'),
    ];

    $groups = $repository->getGroups(1, 'test', true, $rewardTypes);
    expect($groups->first())->toBeInstanceOf(Group::class);
});
