<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Repository\PartnerStatusQueryRepository;
use Database\Connection\ReadDatabaseInterface;
use Mockery;

it('get partner actual status', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerStatusQueryRepository($connection, getDataMapper());
    $partnerId = 1;

    $connection->shouldReceive('function')
        ->once()
        ->withArgs(function ($sql, array $params) use ($partnerId): bool {
            return $params['pEmployee_id'] === $partnerId
                && $params['ptype'] === PartnerStatusQueryRepository::CALCULATE_STATUS;
        })
        ->andReturn((string) PartnerStatusType::NOT_VERIFIED->value)
        ->ordered();

    $partnerStatus = $repository->getActualStatusType($partnerId);
    expect($partnerStatus)->toBe(PartnerStatusType::NOT_VERIFIED);

    $connection->shouldReceive('function')
        ->once()
        ->withArgs(function ($sql, array $params) use ($partnerId): bool {
            return $params['pEmployee_id'] === $partnerId
                && $params['ptype'] === PartnerStatusQueryRepository::CALCULATE_STATUS;
        })
        ->andReturn("1231")
        ->ordered();

    $partnerStatus = $repository->getActualStatusType($partnerId);
    expect($partnerStatus)->toBeNull();
});

it('get partner actual rewards count', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerStatusQueryRepository($connection, getDataMapper());
    $partnerId = 1;
    $funcResult = "2";

    $connection->shouldReceive('function')
        ->once()
        ->withArgs(function ($sql, array $params) use ($partnerId): bool {
            return $params['pEmployee_id'] === $partnerId
                && $params['ptype'] === PartnerStatusQueryRepository::CALCULATE_REWARDS_COUNT;
        })
        ->andReturn($funcResult);

    $actualRewardsCount = $repository->getActualRewardCount($partnerId);
    expect($actualRewardsCount)->toBe((int) $funcResult);
});

it('get partner actual penalties count', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerStatusQueryRepository($connection, getDataMapper());
    $partnerId = 1;
    $funcResult = "2";

    $connection->shouldReceive('function')
        ->once()
        ->withArgs(function ($sql, array $params) use ($partnerId): bool {
            return $params['pEmployee_id'] === $partnerId
                && $params['ptype'] === PartnerStatusQueryRepository::CALCULATE_PENALTIES_COUNT;
        })
        ->andReturn($funcResult);

    $actualRewardsCount = $repository->getActualPenaltiesCount($partnerId);
    expect($actualRewardsCount)->toBe((int) $funcResult);
});

it('get partner saved status', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerStatusQueryRepository($connection, getDataMapper());
    $partnerId = 1;

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($partnerId): bool {
            return $params['partnerId'] === $partnerId;
        })
        ->andReturn((function () {
            yield [
                'id'            => 1,
                'employee_id'   => 1,
                'pd_status_id'  => 1,
                'reward_count'  => 1,
                'penalty_count' => 1,
            ];
        })()
        );

    $savedPartnerStatus = $repository->getPartnerSavedStatus($partnerId);
    expect($savedPartnerStatus)->toBeInstanceOf(PartnerStatus::class);
});
