<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\UseCase\GetAvailablePartnerStatusesUseCase;

it('get list', function (): void {

    $useCase = new GetAvailablePartnerStatusesUseCase();
    $partnerStatuses = $useCase->getList();

    expect($partnerStatuses)->count()->toBe(count(PartnerStatusType::cases()));
});
