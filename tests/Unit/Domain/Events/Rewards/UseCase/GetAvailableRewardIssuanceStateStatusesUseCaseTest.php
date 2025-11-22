<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\UseCase\GetAvailableRewardIssuanceStateStatusesUseCase;

it('get list', function (): void {

    $useCase = new GetAvailableRewardIssuanceStateStatusesUseCase();
    $partnerStatuses = $useCase->getList();

    expect($partnerStatuses)->count()->toBe(count(RewardIssuanceStateStatusType::cases()));
});
