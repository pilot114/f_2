<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Enum\RewardStatusType;
use App\Domain\Events\Rewards\Repository\StatusCommandRepository;
use Database\Connection\ParamType;
use Database\Connection\WriteDatabaseInterface;
use DateTimeInterface;
use Mockery;

it('create status in country', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new StatusCommandRepository($connection, getDataMapper());

    $reward = new Reward(1, "награда", 1, new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')));
    $country = new Country(1, 'Страна 1');
    $status = new RewardStatus(1, RewardStatusType::ACTIVE, $country);
    $userId = 1234;

    $connection->shouldReceive('insert')->once()->withArgs(
        function (string $tableName, array $params, array $types) use ($reward, $status, $userId): bool {
            return $params['pd_present_gds_id'] === $reward->id
                && $params['country_id'] === $status->getCountryId()
                && $params['status'] === $status->getStatusId()
                && $params['dt_from'] instanceof DateTimeInterface
                && $params['cp_emp'] === $userId
                && $types['dt_from'] === ParamType::DATE;
        }
    );

    $repository->createStatusInCountry($reward, $status, $userId);
});

it('update status in country', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new StatusCommandRepository($connection, getDataMapper());

    $reward = new Reward(1, "награда", 1, new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')));
    $country = new Country(1, 'Страна 1');
    $status = new RewardStatus(1, RewardStatusType::ACTIVE, $country);
    $userId = 1234;

    $connection->shouldReceive('update')->once()->withArgs(
        function (string $tableName, array $params, array $condition, array $types) use ($reward, $status, $userId): bool {
            return $params['status'] === $status->getStatusId()
                && $params['dt_from'] instanceof DateTimeInterface
                && $params['cp_emp'] === $userId
                && $condition['pd_present_gds_id'] === $reward->id
                && $condition['country_id'] === $status->getCountryId()
                && $types['dt_from'] === ParamType::DATE;
        }
    );

    $repository->updateStatusInCountry($reward, $status, $userId);
});
