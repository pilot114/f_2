<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Enum;

enum PartnerStatusType: int
{
    case NOT_VERIFIED = 1;
    case TO_AWARD = 2;
    case NOT_AWARDED = 3;
    case EXCLUDED = 4;

    public static function getStatusName(self $statusType): string
    {
        return match ($statusType) {
            PartnerStatusType::NOT_VERIFIED => 'Не проверен',
            PartnerStatusType::TO_AWARD     => 'К выдаче',
            PartnerStatusType::NOT_AWARDED  => 'Не награждается',
            PartnerStatusType::EXCLUDED     => 'Исключен',
        };
    }
}
