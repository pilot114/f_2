<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;

class GetAvailableWorkTimeTimeZoneUseCase
{
    public function getList(): array
    {
        return WorkTimeTimeZone::cases();
    }
}
