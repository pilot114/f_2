<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\PartnerStatusResponse;
use App\Domain\Events\Rewards\DTO\StatusTypeResponse;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'net.pd_employee_status', sequenceName: 'not clear')]
class PartnerStatus
{
    public function __construct(
        #[Column(name: 'id')] public readonly int                 $id,
        #[Column(name: 'partner_id')] public readonly int         $partnerId,
        #[Column(name: 'pd_status_id')] private PartnerStatusType $statusType,
        #[Column(name: 'reward_count')] private ?int              $rewardsCount,
        #[Column(name: 'penalty_count')] private ?int             $penaltiesCount,
        #[Column(name: 'user')] private ?User             $user = null,
    ) {
    }

    public function getName(): string
    {
        return PartnerStatusType::getStatusName($this->statusType);
    }

    public function getStatusType(): PartnerStatusType
    {
        return $this->statusType;
    }

    public function setStatusType(PartnerStatusType $statusType): void
    {
        $this->statusType = $statusType;
    }

    public function getRewardsCount(): ?int
    {
        return $this->rewardsCount;
    }

    public function setRewardsCount(int $rewardsCount): void
    {
        $this->rewardsCount = $rewardsCount;
    }

    public function getPenaltiesCount(): ?int
    {
        return $this->penaltiesCount;
    }

    public function setPenaltiesCount(int $penaltiesCount): void
    {
        $this->penaltiesCount = $penaltiesCount;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function toPartnerStatusResponse(): PartnerStatusResponse
    {
        return new PartnerStatusResponse(
            id: $this->id,
            statusType: new StatusTypeResponse(
                id: $this->statusType->value,
                name: $this->getName()
            ),
            rewardsCount: $this->rewardsCount,
            penaltiesCount: $this->penaltiesCount
        );
    }
}
