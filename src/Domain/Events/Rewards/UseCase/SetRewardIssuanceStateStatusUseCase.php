<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateDto;
use App\Domain\Events\Rewards\DTO\SetRewardIssuanceStateStatusRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Entity\User;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusCommandRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardIssuanceStateCommandRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use DomainException;

class SetRewardIssuanceStateStatusUseCase
{
    public function __construct(
        private RewardIssuanceStateCommandRepository $rewardIssuanceStateCommandRepository,
        private PartnersFullInfoQueryRepository      $partnersQueryRepository,
        private PartnerStatusQueryRepository         $partnerStatusQueryRepository,
        private PartnerStatusCommandRepository       $partnerStatusCommandRepository,
        private TransactionInterface                 $transaction,
        private SecurityUser                         $currentUser,
    ) {
    }

    public function setRewardIssuanceStateStatus(SetRewardIssuanceStateStatusRequest $request): void
    {
        $partner = $this->partnersQueryRepository->getWithActiveRewards(new PartnerFullInfoRequest($request->partnerId));
        $this->transaction->beginTransaction();
        foreach ($request->rewardIssuanceStates as $stateFromRequest) {
            $rewardIssuanceState = $this->getAvailableRewardIssuanceState($stateFromRequest, $partner);
            if (!$rewardIssuanceState instanceof RewardIssuanceState) {
                continue;
            }
            // Если статус выдачи награды совпадает со статусом из запроса, то мы можем поменять только комментарий.
            if ($rewardIssuanceState->getStatus() === $stateFromRequest->status) {
                $this->updateComment($stateFromRequest, $rewardIssuanceState);
            } else {
                $this->updateStatus($stateFromRequest, $rewardIssuanceState);
            }
        }

        $this->setPartnerStatus($partner);
        $this->transaction->commit();
    }

    private function setPartnerStatus(Partner $partner): void
    {
        $penaltiesCount = $this->partnerStatusQueryRepository->getActualPenaltiesCount($partner->id);
        $rewardsCount = $this->partnerStatusQueryRepository->getActualRewardCount($partner->id);
        $savedPartnerStatus = $this->partnerStatusQueryRepository->getPartnerSavedStatus($partner->id);

        // Если количество доступных наград стало 0 устанавливаем "не награждается"
        // Иначе какие-то награды выданы или выданы частично. НО НЕ ВСЕ, а значит пользователь "К выдаче"
        $newStatus = $rewardsCount === 0 ? PartnerStatusType::NOT_AWARDED : PartnerStatusType::TO_AWARD;

        $this->createOrUpdatePartnerStatus(
            partner: $partner,
            newStatus: $newStatus,
            rewardsCount: $rewardsCount,
            penaltiesCount: $penaltiesCount,
            status: $savedPartnerStatus
        );
    }

    private function getAvailableRewardIssuanceState(RewardIssuanceStateDto $stateFromRequest, Partner $partner): ?RewardIssuanceState
    {
        $rewardIssuanceState = $partner->getRewardIssuanceStates()[$stateFromRequest->id] ?? null;
        if ($rewardIssuanceState === null) {
            throw new DomainException('У партера нет состояния выдачи награды с id ' . $stateFromRequest->id);
        }

        if (
            $rewardIssuanceState->getNote() === $stateFromRequest->comment
            && $rewardIssuanceState->getStatus() === $stateFromRequest->status
        ) {
            return null;
        }

        if ($rewardIssuanceState->getStatus() === RewardIssuanceStateStatusType::REWARDED_FULL) {
            return null;
        }

        return $rewardIssuanceState;
    }

    private function updateStatus(
        RewardIssuanceStateDto $stateFromRequest,
        RewardIssuanceState $rewardIssuanceState,
    ): void {
        $rewardIssuanceState->setStatus($stateFromRequest->status);
        $rewardIssuanceState->setRewardDate(new DateTimeImmutable());
        $rewardIssuanceState->setRewardedByUser(new User($this->currentUser->id, $this->currentUser->name));
        if ($stateFromRequest->comment) {
            $rewardIssuanceState->setNote($stateFromRequest->comment);
        }
        $this->rewardIssuanceStateCommandRepository->setStatus($rewardIssuanceState);
    }

    private function updateComment(
        RewardIssuanceStateDto $stateFromRequest,
        RewardIssuanceState $rewardIssuanceState,
    ): void {
        $rewardIssuanceState->setRewardDate(new DateTimeImmutable());
        $rewardIssuanceState->setRewardedByUser(new User($this->currentUser->id, $this->currentUser->name));
        $rewardIssuanceState->setNote($stateFromRequest->comment);
        $this->rewardIssuanceStateCommandRepository->setComment($rewardIssuanceState);
    }

    private function createOrUpdatePartnerStatus(
        Partner $partner,
        PartnerStatusType $newStatus,
        int $rewardsCount,
        int $penaltiesCount,
        ?PartnerStatus $status = null,
    ): void {
        if (!$status instanceof PartnerStatus) {
            $this->createPartnerStatus($partner, $newStatus, $rewardsCount, $penaltiesCount);
        } else {
            $this->updatePartnerStatus($status, $newStatus, $rewardsCount, $penaltiesCount);
        }
    }

    private function updatePartnerStatus(
        PartnerStatus $status,
        PartnerStatusType $newStatus,
        int $rewardsCount,
        int $penaltiesCount,
    ): void {
        $status->setStatusType($newStatus);
        $status->setRewardsCount($rewardsCount);
        $status->setPenaltiesCount($penaltiesCount);
        $status->setUser(new User($this->currentUser->id, $this->currentUser->name));
        $this->partnerStatusCommandRepository->updateStatus($status);
    }

    private function createPartnerStatus(
        Partner $partner,
        PartnerStatusType $newStatus,
        int $rewardsCount,
        int $penaltiesCount,
    ): void {
        $this->partnerStatusCommandRepository->createStatus(
            new PartnerStatus(
                id: Loader::ID_FOR_INSERT,
                partnerId: $partner->id,
                statusType: $newStatus,
                rewardsCount: $rewardsCount,
                penaltiesCount: $penaltiesCount,
                user: new User($this->currentUser->id, $this->currentUser->name)
            )
        );
    }
}
