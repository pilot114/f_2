<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\SetRewardTypeRequest;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Repository\RewardCommandRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\UseCase\SetRewardTypeUseCase;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Mockery;

beforeEach(function (): void {
    $this->rewardQueryRepository = Mockery::mock(RewardQueryRepository::class);
    $this->rewardTypeQueryRepository = Mockery::mock(QueryRepository::class);
    $this->rewardCommandRepository = Mockery::mock(RewardCommandRepository::class);

    $this->useCase = new SetRewardTypeUseCase(
        $this->rewardQueryRepository,
        $this->rewardTypeQueryRepository,
        $this->rewardCommandRepository
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('adds reward type when reward does not have a type', function (): void {
    // Arrange
    $rewardId = 1;
    $typeId = 2;

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);
    $reward = new Reward($rewardId, 'Test Reward', 123, $nomination, null, null);

    $rewardType = new RewardType($typeId, 'Test Type');

    $request = new SetRewardTypeRequest($rewardId, $typeId);

    $this->rewardQueryRepository
        ->shouldReceive('getOne')
        ->with($rewardId)
        ->once()
        ->andReturn($reward);

    $this->rewardTypeQueryRepository
        ->shouldReceive('findOrFail')
        ->with($typeId, "Не найден тип награды с id = {$typeId}")
        ->once()
        ->andReturn($rewardType);

    $this->rewardCommandRepository
        ->shouldReceive('addRewardType')
        ->with(123, $typeId)
        ->once();

    // Act
    $result = $this->useCase->setRewardType($request);

    // Assert
    expect($result)->toBe($reward);
    expect($reward->getRewardType())->toBe($rewardType);
});

it('changes reward type when reward already has a type', function (): void {
    // Arrange
    $rewardId = 1;
    $typeId = 2;
    $oldTypeId = 3;

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);
    $oldRewardType = new RewardType($oldTypeId, 'Old Type');
    $reward = new Reward($rewardId, 'Test Reward', 123, $nomination, null, $oldRewardType);

    $newRewardType = new RewardType($typeId, 'New Type');

    $request = new SetRewardTypeRequest($rewardId, $typeId);

    $this->rewardQueryRepository
        ->shouldReceive('getOne')
        ->with($rewardId)
        ->once()
        ->andReturn($reward);

    $this->rewardTypeQueryRepository
        ->shouldReceive('findOrFail')
        ->with($typeId, "Не найден тип награды с id = {$typeId}")
        ->once()
        ->andReturn($newRewardType);

    $this->rewardCommandRepository
        ->shouldReceive('changeRewardType')
        ->with(123, $typeId)
        ->once();

    // Act
    $result = $this->useCase->setRewardType($request);

    // Assert
    expect($result)->toBe($reward);
    expect($reward->getRewardType())->toBe($newRewardType);
});

it('delete reward type', function (): void {
    // Arrange
    $rewardId = 1;

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);
    $reward = new Reward($rewardId, 'Test Reward', 123, $nomination, null, null);

    $request = new SetRewardTypeRequest($rewardId, null);

    $this->rewardQueryRepository
        ->shouldReceive('getOne')
        ->with($rewardId)
        ->once()
        ->andReturn($reward);

    $this->rewardCommandRepository
        ->shouldReceive('deleteRewardType')
        ->with(123)
        ->once();

    // Act
    $result = $this->useCase->setRewardType($request);

    // Assert
    expect($result)->toBe($reward);
    expect($reward->getRewardType())->toBeNull();
});

it('throws exception when reward not found', function (): void {
    // Arrange
    $rewardId = 999;
    $typeId = 2;

    $request = new SetRewardTypeRequest($rewardId, $typeId);

    $this->rewardQueryRepository
        ->shouldReceive('getOne')
        ->with($rewardId)
        ->once()
        ->andThrow(new EntityNotFoundDatabaseException("Не найдена награда с id = {$rewardId}"));

    // Act & Assert
    $this->expectException(EntityNotFoundDatabaseException::class);
    $this->expectExceptionMessage("Не найдена награда с id = {$rewardId}");

    $this->useCase->setRewardType($request);
});

it('throws exception when reward type not found', function (): void {
    // Arrange
    $rewardId = 1;
    $typeId = 999;

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);
    $reward = new Reward($rewardId, 'Test Reward', 123, $nomination, null, null);

    $request = new SetRewardTypeRequest($rewardId, $typeId);

    $this->rewardQueryRepository
        ->shouldReceive('getOne')
        ->with($rewardId)
        ->once()
        ->andReturn($reward);

    $this->rewardTypeQueryRepository
        ->shouldReceive('findOrFail')
        ->with($typeId, "Не найден тип награды с id = {$typeId}")
        ->once()
        ->andThrow(new EntityNotFoundDatabaseException("Не найден тип награды с id = {$typeId}"));

    // Act & Assert
    $this->expectException(EntityNotFoundDatabaseException::class);
    $this->expectExceptionMessage("Не найден тип награды с id = {$typeId}");

    $this->useCase->setRewardType($request);
});
