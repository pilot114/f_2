<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use App\Domain\Events\Rewards\DTO\RewardResponse;
use App\Domain\Events\Rewards\DTO\RewardStatusResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.pd_present_gds')]
class Reward
{
    public function __construct(
        #[Column] public readonly int                              $id,
        #[Column] public readonly string                           $name,
        #[Column(name: 'product_id')] public readonly int          $productId,
        #[Column] private Nomination                               $nomination,
        #[Column] private ?string                                  $commentary = null,
        #[Column] private ?RewardType                              $type = null,
        /** @var array<int, RewardStatus> */
        #[Column(collectionOf: RewardStatus::class)] private array $statuses = [],
    ) {
    }

    public function toRewardResponse(): RewardResponse
    {
        return new RewardResponse(
            id: $this->id,
            name: $this->name,
            commentary: $this->commentary,
            statuses: $this->getStatuses()
        );
    }

    public function getComment(): ?string
    {
        return $this->commentary;
    }

    /** @return  RewardStatusResponse[] */
    public function getStatuses(): array
    {
        return array_values(array_map(fn (RewardStatus $status): RewardStatusResponse => $status->toRewardStatusResponse(), $this->statuses));
    }

    public function setComment(?string $comment): void
    {
        $this->commentary = $comment;
    }

    public function setRewardType(?RewardType $rewardType): void
    {
        $this->type = $rewardType;
    }

    public function getRewardType(): ?RewardType
    {
        return $this->type;
    }

    public function findStatusInCountry(Country $country): ?RewardStatus
    {
        foreach ($this->statuses as $existingStatus) {
            if ($existingStatus->getCountryId() === $country->id) {
                return $existingStatus;
            }
        }

        return null;
    }

    public function getNomination(): Nomination
    {
        return $this->nomination;
    }

    public function toRewardFullResponse(): RewardFullResponse
    {
        return new RewardFullResponse(
            id: $this->id,
            name: $this->name,
            commentary: $this->commentary,
            statuses: $this->getStatuses(),
            type: $this->type?->toRewardTypeResponse()
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'commentary' => $this->commentary,
            'statuses'   => $this->getStatuses(),
        ];
    }
}
