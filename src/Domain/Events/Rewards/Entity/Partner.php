<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\ContractResponse;
use App\Domain\Events\Rewards\DTO\PartnerByEventResponse;
use App\Domain\Events\Rewards\DTO\PartnerFullInfoResponse;
use App\Domain\Events\Rewards\DTO\PartnerResponse;
use App\Domain\Events\Rewards\DTO\PenaltyResponse;
use App\Domain\Events\Rewards\DTO\RewardByEventResponse;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateResponse;
use App\Domain\Events\Rewards\DTO\TicketResponse;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;

#[Entity(name: 'net.employee', sequenceName: 'not clear')]
class Partner
{
    /** @var Registration[] */
    private array $registrations = [];

    public function __construct(
        #[Column] public readonly int $id,
        #[Column] private string $name,
        #[Column] private string $contract,
        #[Column] private Country $country,
        #[Column(name: 'is_family')] private bool $isFamily,
        #[Column] private ?Rang $rang = null,
        #[Column(name: 'status')] private ?PartnerStatus $status = null,
        #[Column(name: 'actual_status')] private ?PartnerStatusType $actualStatusType = null,
        #[Column(name: 'actual_reward_count')] private ?int $actualRewardsCount = null,
        #[Column(name: 'actual_penalty_count')] private ?int $actualPenaltiesCount = null,
        /** @var array<int, Penalty> */
        #[Column(collectionOf: Penalty::class)] private array $penalties = [],
        /** @var array<int, RewardIssuanceState> */
        #[Column(name: 'rewardissuancestate', collectionOf: RewardIssuanceState::class)]
        private array $rewardIssuanceStates = [],
    ) {
        $this->setActualDataToPartnerStatus();
    }

    public function getRang(): ?Rang
    {
        return $this->rang;
    }

    public function getPenalties(): array
    {
        return $this->penalties;
    }

    public function getStatus(): ?PartnerStatus
    {
        return $this->status;
    }

    public function getFilteredRewardsCount(): int
    {
        return count($this->rewardIssuanceStates);
    }

    /** @return array<int, RewardIssuanceState> */
    public function getRewardIssuanceStates(): array
    {
        return $this->rewardIssuanceStates;
    }

    /** @param array<int, RewardIssuanceState> $deletedRewards */
    public function addDeletedRewards(array $deletedRewards): void
    {
        $this->rewardIssuanceStates = array_merge($this->rewardIssuanceStates, $deletedRewards);
    }

    /** @return int[] */
    public function getNominationIds(): array
    {
        $nominationIds = [];

        foreach ($this->rewardIssuanceStates as $rewardIssuanceState) {
            $nominationIds[] = $rewardIssuanceState->nomination->id;
        }

        return array_values(array_unique($nominationIds));
    }

    /** @return int[] */
    public function getCalculationResultIds(): array
    {
        $calculationResultIds = [];

        foreach ($this->rewardIssuanceStates as $rewardIssuanceState) {
            $calculationResultIds[] = $rewardIssuanceState->calculationResultId;
        }

        return array_values(array_unique($calculationResultIds));
    }

    /** @return array<int, array<int, RewardIssuanceState>> */
    public function getRewardsGroupedByNomination(): array
    {
        $groupedRewards = [];

        foreach ($this->rewardIssuanceStates as $rewardIssuanceState) {
            $nominationId = $rewardIssuanceState->nomination->id;
            $groupedRewards[$nominationId][] = $rewardIssuanceState;
        }

        return $groupedRewards;
    }

    public function addRegistrations(array $registrations): void
    {
        $this->registrations = $registrations;
    }

    public function toTicketResponse(): TicketResponse
    {
        $registrationsDates = array_map(fn (Registration $registration): string => $registration->registrationDate->format('Y-m-d'), $this->registrations);

        return new TicketResponse(
            count: count($this->registrations),
            registrationDates: array_unique($registrationsDates)
        );
    }

    public function getTickets(): array
    {
        $registrations = array_map(fn (Registration $registration): string => $registration->registrationDate->format('Y-m-d'), $this->registrations);

        return [
            'count'             => count($this->registrations),
            'registrationDates' => array_unique($registrations),
        ];
    }

    public function toPartnerCommonResponse(): PartnerFullInfoResponse
    {
        return new PartnerFullInfoResponse(
            id: $this->id,
            name: $this->name,
            contract: $this->contract,
            country: $this->country->toCountryResponse(),
            isFamily: $this->isFamily,
            tickets: $this->toTicketResponse()
        );
    }

    /** @return PartnerResponse[] */
    public function toPartnerByContractResponse(): array
    {
        $items = [];
        $rewardsGroupedByNomination = $this->getRewardsGroupedByNomination();

        foreach ($rewardsGroupedByNomination as $rewardIssuanceStates) {
            $awards = [];
            $rewardIssuanceState = null;

            foreach ($rewardIssuanceStates as $rewardIssuanceState) {
                $awards[] = $rewardIssuanceState->toAwardsResponse();
            }

            if (is_null($rewardIssuanceState)) {
                continue;
            }

            $items[] = new PartnerResponse(
                id: $this->id,
                contract: new ContractResponse(
                    name: $this->name,
                    contract: $this->contract,
                    isFamily: $this->isFamily
                ),
                country: $this->country->name,
                deleted: $rewardIssuanceState->deleted,
                program: $rewardIssuanceState->program->name,
                nomination: $rewardIssuanceState->toNominationResponse(),
                awards: $awards,
                penalties: array_map(fn (Penalty $penalty): PenaltyResponse => $penalty->toPenaltyResponse(), array_values($this->getPenalties())),
            );
        }

        return $items;
    }

    public function toPartnerByEventResponse(): PartnerByEventResponse
    {
        return new PartnerByEventResponse(
            id: $this->id,
            name: $this->name,
            contract: $this->contract,
            country: $this->country->toCountryResponse(),
            isFamily: $this->isFamily,
            tickets: $this->toTicketResponse(),
            status: $this->getStatus()?->toPartnerStatusResponse(),
            calculationResultIds: $this->getCalculationResultIds(),
            rewards: array_map(
                fn (RewardIssuanceState $rewardIssuanceState): RewardByEventResponse => new RewardByEventResponse(
                    name: $rewardIssuanceState->reward->name,
                    winDate: $rewardIssuanceState->winDate->format(DateTimeImmutable::ATOM),
                    calculationResultId: $rewardIssuanceState->calculationResultId,
                    count: $rewardIssuanceState->rewardsCount
                ),
                array_values($this->getRewardIssuanceStates())
            )
        );
    }

    public function toPartnerFullResponse(): PartnerFullInfoResponse
    {
        return new PartnerFullInfoResponse(
            id: $this->id,
            name: $this->name,
            contract: $this->contract,
            country: $this->country->toCountryResponse(),
            isFamily: $this->isFamily,
            tickets: $this->toTicketResponse(),
            rang: $this->getRang()?->toRangResponse(),
            status: $this->getStatus()?->toPartnerStatusResponse(),
            penalties: array_map(fn (Penalty $penalty): PenaltyResponse => $penalty->toPenaltyResponse(), array_values($this->getPenalties())),
            rewardIssuanceState: array_map(
                fn (RewardIssuanceState $rewardIssuanceState): RewardIssuanceStateResponse => $rewardIssuanceState->toRewardIssuanceStateResponse(),
                array_values($this->getRewardIssuanceStates())
            )
        );
    }

    private function setActualDataToPartnerStatus(): void
    {
        if (
            !$this->status instanceof PartnerStatus
            && $this->actualStatusType instanceof PartnerStatusType
            && ($this->actualPenaltiesCount !== null && $this->actualRewardsCount >= 0)
            && ($this->actualRewardsCount !== null && $this->actualPenaltiesCount >= 0)
        ) {
            $this->status = new PartnerStatus(
                Loader::ID_FOR_INSERT,
                $this->id,
                $this->actualStatusType,
                $this->actualRewardsCount,
                $this->actualPenaltiesCount
            );
        }
        if (!is_null($this->actualStatusType)) {
            $this->status?->setStatusType($this->actualStatusType);
        }
        if (!is_null($this->actualRewardsCount) && $this->actualRewardsCount >= 0) {
            $this->status?->setRewardsCount($this->actualRewardsCount);
        }
        if (!is_null($this->actualPenaltiesCount) && $this->actualPenaltiesCount >= 0) {
            $this->status?->setPenaltiesCount($this->actualPenaltiesCount);
        }
    }
}
