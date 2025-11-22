<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\RewardStatusResponse;
use App\Domain\Events\Rewards\Enum\RewardStatusType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.pd_present_gds_country_status')]
class RewardStatus
{
    public function __construct(
        #[Column(name: 'id')] public readonly int          $id,
        #[Column(name: 'status')] private RewardStatusType $status,
        #[Column] private Country                          $country,
    ) {
    }

    public function getName(): string
    {
        return match ($this->status) {
            RewardStatusType::ACTIVE  => 'Актуальный',
            RewardStatusType::ARCHIVE => 'Архив'
        };
    }

    public function getStatusId(): int
    {
        return $this->status->value;
    }

    public function getCountryId(): int
    {
        return $this->country->id;
    }

    public function toRewardStatusResponse(): RewardStatusResponse
    {
        return new RewardStatusResponse(
            id: $this->getStatusId(),
            name: $this->getName(),
            country: $this->country->toCountryResponse()
        );
    }
}
