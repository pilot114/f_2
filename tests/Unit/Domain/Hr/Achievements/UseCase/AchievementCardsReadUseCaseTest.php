<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use App\Domain\Hr\Achievements\UseCase\AchievementCardsReadUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->readRepository = $this->createMock(AchievementQueryRepository::class);
    $this->useCase = new AchievementCardsReadUseCase($this->readRepository);
});

it('gets achievement cards list', function (): void {
    $image = new Image(1, 123, 'Test Image');
    $achievement1 = new Achievement(1, 'Achievement 1', 'Description 1', $image);
    $achievement2 = new Achievement(2, 'Achievement 2', 'Description 2', $image);
    $achievements = new Collection([$achievement1, $achievement2]);

    $this->readRepository
        ->expects($this->once())
        ->method('getList')
        ->willReturn($achievements);

    $result = $this->useCase->getAchievementCards();

    expect($result)->toBe($achievements);
    expect($result)->toHaveCount(2);
});

it('gets achievement card by id', function (): void {
    $image = new Image(1, 123, 'Test Image');
    $achievement = new Achievement(1, 'Test Achievement', 'Description', $image);

    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($achievement);

    $result = $this->useCase->getAchievementCardById(1);

    expect($result)->toBe($achievement);
});

it('returns null when achievement not found by id', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    $result = $this->useCase->getAchievementCardById(999);

    expect($result)->toBeNull();
});

it('checks if card name exists', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('nameExist')
        ->with('Test Name')
        ->willReturn(true);

    $result = $this->useCase->cardIsExist('Test Name');

    expect($result)->toBeTrue();
});

it('checks if card name does not exist', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('nameExist')
        ->with('Non Existent Name')
        ->willReturn(false);

    $result = $this->useCase->cardIsExist('Non Existent Name');

    expect($result)->toBeFalse();
});
