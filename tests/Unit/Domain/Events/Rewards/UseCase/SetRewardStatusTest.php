<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Enum\RewardStatusType;
use App\Domain\Events\Rewards\Repository\CountryQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\Repository\StatusCommandRepository;
use App\Domain\Events\Rewards\UseCase\SetRewardStatusUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Mockery;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function (): void {
    $this->readReward = Mockery::mock(RewardQueryRepository::class);
    $this->readCountry = Mockery::mock(CountryQueryRepository::class);
    $this->writeStatus = Mockery::mock(StatusCommandRepository::class);
    $this->currentUser = createSecurityUser(1234, 'ФИО', 'email@mail.com');
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new SetRewardStatusUseCase(
        $this->readReward,
        $this->readCountry,
        $this->writeStatus,
        $this->currentUser,
        $this->transaction,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('set reward status', function (): void {
    $rewardId = 1;
    $activeInCountries = [1, 2];
    $archiveInCountries = [3, 4];
    $activeCountriesCollection = EnumerableWithTotal::build(
        [
            new Country(1, 'Страна 1'),
            new Country(2, 'Страна 2'),
        ]
    );
    $archiveCountriesCollection = EnumerableWithTotal::build(
        [
            new Country(3, 'Страна 3'),
            new Country(4, 'Страна 4'),
        ]
    );

    $currentUser = createSecurityUser(1234, 'ФИО', 'email@mail.com');

    $existingArchiveStatus = new RewardStatus(Loader::ID_FOR_INSERT, RewardStatusType::ARCHIVE, $archiveCountriesCollection[0]);
    $existingActiveStatus = new RewardStatus(Loader::ID_FOR_INSERT, RewardStatusType::ACTIVE, $archiveCountriesCollection[1]);
    $reward = new Reward(
        1,
        'награда 1',
        1,
        new Nomination(
            1,
            'Номинация 1',
            new Program(1, 'Программа 1')
        ),
        null,
        new RewardType(1, 'Денежный приз'),
        [$existingArchiveStatus, $existingActiveStatus]
    );

    $this->readReward->shouldReceive('getOne')->with($rewardId)->andReturn($reward);
    $this->readCountry->shouldReceive('getByIds')->with($activeInCountries)->andReturn($activeCountriesCollection);
    $this->readCountry->shouldReceive('getByIds')->with($archiveInCountries)->andReturn($archiveCountriesCollection);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->writeStatus->shouldReceive('createStatusInCountry')->times(2);
    $this->writeStatus->shouldReceive('updateStatusInCountry')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardStatus($rewardId, $activeInCountries, $archiveInCountries);
});

it('set reward intersect statuses', function (): void {
    $rewardId = 1;
    $activeInCountries = [1, 2];
    $archiveInCountries = [2, 4];

    $this->expectException(ConflictHttpException::class);

    $this->useCase->setRewardStatus($rewardId, $activeInCountries, $archiveInCountries);
});
