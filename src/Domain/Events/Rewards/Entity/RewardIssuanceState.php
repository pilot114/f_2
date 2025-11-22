<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\AwardsResponse;
use App\Domain\Events\Rewards\DTO\CalculationResultIdResponse;
use App\Domain\Events\Rewards\DTO\CalculationResultResponse;
use App\Domain\Events\Rewards\DTO\NominationResponse;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateResponse;
use App\Domain\Events\Rewards\DTO\StatusResponse;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'net.pd_reward_status', sequenceName: 'not clear')]
class RewardIssuanceState
{
    private ?int $eventId = null;

    public function __construct(
        #[Column] public readonly int                                 $id,
        #[Column(name: 'calculation_result_id')] public readonly int  $calculationResultId,
        #[Column] public readonly Program                             $program,
        #[Column] public readonly Nomination                          $nomination,
        #[Column(name: 'count')] public readonly int                  $rewardsCount,
        #[Column] private RewardIssuanceStateStatusType               $status,
        #[Column(name: 'win_date')] public readonly DateTimeImmutable $winDate,
        #[Column] public readonly Reward                              $reward,
        #[Column] private ?string                                     $note = null,
        #[Column(name: 'rewarddate')] private ?DateTimeImmutable      $rewardDate = null,
        #[Column(name: 'user')] private ?User                         $rewardedByUser = null,
        #[Column(name: 'deleted')] public readonly bool               $deleted = false,
    ) {
    }

    public function getStatus(): RewardIssuanceStateStatusType
    {
        return $this->status;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(?int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function setRewardDate(?DateTimeImmutable $rewardDate): void
    {
        $this->rewardDate = $rewardDate;
    }

    public function setStatus(RewardIssuanceStateStatusType $status): void
    {
        $this->status = $status;
    }

    public function getRewardDate(): ?DateTimeImmutable
    {
        return $this->rewardDate;
    }

    public function getRewardedByUser(): ?User
    {
        return $this->rewardedByUser;
    }

    public function setRewardedByUser(?User $rewardedByUser): void
    {
        $this->rewardedByUser = $rewardedByUser;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function toAwardsResponse(): AwardsResponse
    {
        return new AwardsResponse(
            name: $this->reward->name,
            calculationResult: new CalculationResultIdResponse(
                id: $this->calculationResultId
            ),
            comment: $this->note,
            count: $this->rewardsCount,
            status: new StatusResponse(
                id: $this->status->value,
                name: RewardIssuanceStateStatusType::getStatusName($this->status),
                date: $this->rewardDate?->format('d.m.Y') ?? '',
                user: $this->rewardedByUser?->getShortName() ?? ''
            )
        );
    }

    public function toNominationResponse(): NominationResponse
    {
        return new NominationResponse(
            id: $this->nomination->id,
            name: $this->nomination->name,
            date: $this->winDate->format('Y-m-d'),
        );
    }

    public function toRewardIssuanceStateResponse(): RewardIssuanceStateResponse
    {
        return new RewardIssuanceStateResponse(
            id: $this->id,
            program: $this->program->toProgramResponse(),
            nomination: $this->nomination->toNominationResponse($this->winDate->format('Y-m-d')),
            calculationResult: new CalculationResultResponse(
                id: $this->calculationResultId
            ),
            rewardsCount: $this->rewardsCount,
            reward: $this->reward->toRewardResponse(),
            status: $this->status->toStatusResponse(),
            winDate: $this->winDate->format(DateTimeImmutable::ATOM),
            rewardDate: $this->rewardDate?->format(DateTimeImmutable::ATOM),
            rewardedByUser: $this->rewardedByUser?->toUserResponse(),
            note: $this->note,
            deleted: $this->deleted
        );
    }
}
