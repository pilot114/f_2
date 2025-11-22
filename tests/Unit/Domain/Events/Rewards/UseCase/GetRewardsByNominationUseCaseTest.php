<?php

declare(strict_types=1);

use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Enum\RewardStatusType;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetRewardsByNominationUseCase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->rewardQueryRepository = Mockery::mock(RewardQueryRepository::class);
    $this->useCase = new GetRewardsByNominationUseCase($this->rewardQueryRepository);
});

afterEach(function (): void {
    Mockery::close();
});

test('getNominationWithRewards returns nomination with rewards', function (): void {
    // Arrange
    $rewardId = 1;
    $countryId = 10;

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(5, 'Test Nomination', $program, []);

    // Создаем настоящие объекты Reward
    $country1 = new Country(1, 'Country 1');
    $country2 = new Country(2, 'Country 2');
    $rewardStatus1 = new RewardStatus(1, RewardStatusType::ACTIVE, $country1);
    $rewardStatus2 = new RewardStatus(2, RewardStatusType::ARCHIVE, $country2);

    $reward1 = new Reward(
        1,
        'Reward 1',
        123,
        $nomination,
        'Comment 1',
        new RewardType(1, 'Type 1'),
        [$rewardStatus1, $rewardStatus2]
    );

    $reward2 = new Reward(
        2,
        'Reward 2',
        456,
        $nomination,
        'Comment 2',
        new RewardType(2, 'Type 2'),
        [$rewardStatus1]
    );

    $rewards = new Collection([$reward1, $reward2]);

    $this->rewardQueryRepository
        ->shouldReceive('getRewardsInNomination')
        ->with($rewardId, $countryId)
        ->once()
        ->andReturn($rewards);

    // Act
    $result = $this->useCase->getNominationWithRewards($rewardId, $countryId);

    // Assert
    expect($result)->toBeInstanceOf(Nomination::class)
        ->and($result->id)->toBe(5)
        ->and($result->name)->toBe('Test Nomination')
        ->and($result->getProgram())->toBeInstanceOf(Program::class)
        ->and($result->getProgram()->id)->toBe(1)
        ->and($result->getRewards())->toBeArray()
        ->and($result->getRewards())->toHaveCount(2)
        ->and($result->getRewards()[0])->toBeInstanceOf(RewardFullResponse::class)
        ->and($result->getRewards()[0]->name)->toBe('Reward 1')
        ->and($result->getRewards()[1]->name)->toBe('Reward 2');
});

test('getNominationWithRewards throws NotFoundHttpException when nomination not found', function (): void {
    // Arrange
    $rewardId = 999;
    $countryId = 10;

    $this->rewardQueryRepository
        ->shouldReceive('getRewardsInNomination')
        ->with($rewardId, $countryId)
        ->once()
        ->andReturn(new Collection([]));

    // Act & Assert
    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Не найдена номинация в которой есть награда с id 999');

    $this->useCase->getNominationWithRewards($rewardId, $countryId);
});
