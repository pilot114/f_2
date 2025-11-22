<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Enum\RewardStatusType;

it('creates reward status', function (): void {
    $country = new Country(1, 'Россия');

    $rewardStatus = new RewardStatus(
        id: 1,
        status: RewardStatusType::ACTIVE,
        country: $country
    );

    expect($rewardStatus->id)->toBe(1);
    expect($rewardStatus->getStatusId())->toBe(1);
    expect($rewardStatus->getCountryId())->toBe(1);
});

it('returns status name for active', function (): void {
    $country = new Country(1, 'Россия');

    $rewardStatus = new RewardStatus(
        id: 1,
        status: RewardStatusType::ACTIVE,
        country: $country
    );

    expect($rewardStatus->getName())->toBe('Актуальный');
});

it('returns status name for archive', function (): void {
    $country = new Country(1, 'Россия');

    $rewardStatus = new RewardStatus(
        id: 2,
        status: RewardStatusType::ARCHIVE,
        country: $country
    );

    expect($rewardStatus->getName())->toBe('Архив');
});

it('converts to array', function (): void {
    $country = new Country(1, 'Россия');

    $rewardStatus = new RewardStatus(
        id: 1,
        status: RewardStatusType::ACTIVE,
        country: $country
    );

    $result = $rewardStatus->toRewardStatusResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Актуальный');
    expect($result->country->id)->toBe($country->toCountryResponse()->id);
    expect($result->country->name)->toBe($country->toCountryResponse()->name);
});
