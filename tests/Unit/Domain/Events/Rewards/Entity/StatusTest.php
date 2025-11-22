<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Enum\RewardStatusType;

it('create status test', function (): void {

    $statusId = 123;
    $statusType = RewardStatusType::ACTIVE;
    $countryId = 1;
    $countryName = 'name';
    $country = new Country($countryId, $countryName);
    $status = new RewardStatus($statusId, $statusType, $country);

    $statusResponse = $status->toRewardStatusResponse();

    expect($status->id)->toBe($statusId);
    expect($statusResponse->id)->toBe($statusType->value);
    expect($statusResponse->name)->toBe('Актуальный');
    expect($statusResponse->country->id)->toBe($countryId);
    expect($statusResponse->country->name)->toBe($countryName);
});
