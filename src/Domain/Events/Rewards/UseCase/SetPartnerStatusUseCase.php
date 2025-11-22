<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateDto;
use App\Domain\Events\Rewards\DTO\SetPartnerStatusRequest;
use App\Domain\Events\Rewards\Entity\Event;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Entity\User;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\EventQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusCommandRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardIssuanceStateCommandRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use DomainException;

class SetPartnerStatusUseCase
{
    public function __construct(
        private PartnerStatusCommandRepository       $partnerStatusCommandRepository,
        private RewardIssuanceStateCommandRepository $rewardIssuanceStateCommandRepository,
        private PartnersFullInfoQueryRepository      $partnersQueryRepository,
        private PartnerStatusQueryRepository         $partnerStatusQueryRepository,
        private EventQueryRepository                 $eventQueryRepository,
        private SecurityUser                         $currentUser,
        private TransactionInterface                 $transaction,
    ) {
    }

    public function setPartnerStatus(SetPartnerStatusRequest $request): void
    {
        $partner = $this->partnersQueryRepository->getWithActiveRewards(new PartnerFullInfoRequest($request->partnerId));
        $this->checkPartnerStatusCanBeChanged($request, $partner);

        $event = $this->eventQueryRepository->getEventByIdFromAllowedList($request->eventId);
        $this->transaction->beginTransaction();
        $this->setRewardIssuanceStateStatus($request->rewardIssuanceStates, $partner, $event, $request->partnerStatus);

        $actualStatusType = $this->partnerStatusQueryRepository->getActualStatusType($partner->id);
        $actualRewardsCount = $this->partnerStatusQueryRepository->getActualRewardCount($partner->id);
        $actualPenaltiesCount = $this->partnerStatusQueryRepository->getActualPenaltiesCount($partner->id);
        $savedStatus = $this->partnerStatusQueryRepository->getPartnerSavedStatus($partner->id);

        // 1. Если пользователь изменил статус всех доступных наград на "Выдано полностью",
        // сохранять статус "Не награждается" (независимо от выбранного пользователем)

        if ($actualStatusType === PartnerStatusType::NOT_AWARDED) {
            $this->createOrUpdateStatus(
                partner: $partner,
                newStatus: $actualStatusType,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
                status: $savedStatus
            );
            $this->transaction->commit();
            return;
        }

        // 2. если employee_actual_status = 1 и employee_saved_status is null
        // и пользователь выбрал статус "К выдаче" или "Исключен",
        // сохранять выбранный пользователем статус
        if (
            $actualStatusType === PartnerStatusType::NOT_VERIFIED
            && is_null($savedStatus)
            && ($request->partnerStatus === PartnerStatusType::TO_AWARD
            || $request->partnerStatus === PartnerStatusType::EXCLUDED)
        ) {
            $this->createStatus(
                partner: $partner,
                newStatus: $request->partnerStatus,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
            );
            $this->transaction->commit();
            return;
        }

        // 3 если employee_actual_status not null
        // и пользователь не выбрал никакой статус,
        // сохранять employee_actual_status
        if (
            $actualStatusType instanceof PartnerStatusType
            && is_null($request->partnerStatus)
        ) {
            $this->createOrUpdateStatus(
                partner: $partner,
                newStatus: $actualStatusType,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
                status: $savedStatus
            );
            $this->transaction->commit();
            return;
        }

        // 4. если employee_actual_status = 1 и employee_saved_status = статусу выбранному пользователем,
        // не обновлять статус, но обновить количество доступных наград и количество штрафов
        // TODO тут вопрос. Статус и так не обновится. Или имеется ввиду, что не надо обновлять последнего редактора???
        if (
            $actualStatusType === PartnerStatusType::NOT_VERIFIED
            && $savedStatus instanceof PartnerStatus
            && $savedStatus->getStatusType() === $request->partnerStatus
        ) {
            $this->updateCountsOnly(
                status: $savedStatus,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
            );
            $this->transaction->commit();
            return;
        }

        // 5. если employee_actual_status = 1
        // и employee_saved_status != статусу выбранному пользователем,
        // сохранять выбранный пользователем статус

        if (
            $actualStatusType === PartnerStatusType::NOT_VERIFIED
            && $savedStatus instanceof PartnerStatus
            && $request->partnerStatus instanceof PartnerStatusType
            && $savedStatus->getStatusType() !== $request->partnerStatus
        ) {
            $this->updateStatus(
                status: $savedStatus,
                newStatus: $request->partnerStatus,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
            );
            $this->transaction->commit();
            return;
        }

        // 6. если employee_actual_status in null
        // и employee_saved_status not null,
        // сохранять выбранный пользователем статус
        if (
            is_null($actualStatusType)
            && $savedStatus instanceof PartnerStatus
            && $request->partnerStatus instanceof PartnerStatusType
        ) {
            $this->updateStatus(
                status: $savedStatus,
                newStatus: $request->partnerStatus,
                rewardsCount: $actualRewardsCount,
                penaltiesCount: $actualPenaltiesCount,
            );
        }
        $this->transaction->commit();
    }

    /**
     * @param RewardIssuanceStateDto[] $rewardStates
     */
    private function setRewardIssuanceStateStatus(
        array $rewardStates,
        Partner $partner,
        Event $event,
        ?PartnerStatusType $newPartnerStatus = null
    ): void {
        // Обработка смены статусов состояния выдачи наград
        foreach ($rewardStates as $stateFromRequest) {
            // Если пользователь выбрал статус контракта "Исключен", не сохранять статусы выдачи наград
            if ($newPartnerStatus === PartnerStatusType::EXCLUDED) {
                break;
            }
            $rewardIssuanceState = $this->getAvailableRewardIssuanceState($stateFromRequest, $partner);
            if (!$rewardIssuanceState instanceof RewardIssuanceState) {
                continue;
            }
            // Если статус выдачи награды совпадает со статусом из запроса, то мы можем поменять только комментарий.
            if ($rewardIssuanceState->getStatus() === $stateFromRequest->status) {
                $this->updateRewardIssuanceStateComment($stateFromRequest, $rewardIssuanceState);
            } else {
                $this->updateRewardIssuanceStateStatus($stateFromRequest, $rewardIssuanceState, $event);
            }
        }
    }

    private function createOrUpdateStatus(
        Partner $partner,
        PartnerStatusType $newStatus,
        int $rewardsCount,
        int $penaltiesCount,
        ?PartnerStatus $status = null,
    ): void {
        if (!$status instanceof PartnerStatus) {
            $this->createStatus($partner, $newStatus, $rewardsCount, $penaltiesCount);
        } else {
            $this->updateStatus($status, $newStatus, $rewardsCount, $penaltiesCount);
        }
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

    private function checkPartnerStatusCanBeChanged(SetPartnerStatusRequest $request, Partner $partner): void
    {
        if ($request->partnerStatus === PartnerStatusType::NOT_AWARDED) {
            throw new DomainException('Нельзя вручную назначить статус "Не награждается"');
        }

        // Если статус контракта изменен с "Не проверен" на "К выдаче" или "Исключен", то вручную вернуть статус на "Не проверен" невозможно,
        if (
            in_array($partner->getStatus()?->getStatusType(), [PartnerStatusType::EXCLUDED, PartnerStatusType::TO_AWARD], true)
            && $request->partnerStatus === PartnerStatusType::NOT_VERIFIED
        ) {
            throw new DomainException('Если статус контракта изменен с "Не проверен" на "К выдаче" или "Исключен", то вручную вернуть статус на "Не проверен" невозможно');
        }

        if ($partner->getStatus()?->getStatusType() === PartnerStatusType::NOT_AWARDED) {
            throw new DomainException("нельзя вручную менять статус у партнёров со статусом 'Не награждается'");
        }
    }

    private function updateRewardIssuanceStateStatus(
        RewardIssuanceStateDto $stateFromRequest,
        RewardIssuanceState $rewardIssuanceState,
        Event $event
    ): void {
        $rewardIssuanceState->setEventId($event->id);
        $rewardIssuanceState->setStatus($stateFromRequest->status);
        $rewardIssuanceState->setRewardDate(new DateTimeImmutable());
        $rewardIssuanceState->setRewardedByUser(new User($this->currentUser->id, $this->currentUser->name));
        if ($stateFromRequest->comment) {
            $rewardIssuanceState->setNote($stateFromRequest->comment);
        }
        $this->rewardIssuanceStateCommandRepository->setStatus($rewardIssuanceState);
    }

    private function updateRewardIssuanceStateComment(
        RewardIssuanceStateDto $stateFromRequest,
        RewardIssuanceState $rewardIssuanceState,
    ): void {
        $rewardIssuanceState->setRewardDate(new DateTimeImmutable());
        $rewardIssuanceState->setRewardedByUser(new User($this->currentUser->id, $this->currentUser->name));
        $rewardIssuanceState->setNote($stateFromRequest->comment);
        $this->rewardIssuanceStateCommandRepository->setComment($rewardIssuanceState);
    }

    private function updateStatus(
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

    private function updateCountsOnly(
        PartnerStatus $status,
        int $rewardsCount,
        int $penaltiesCount,
    ): void {
        $status->setRewardsCount($rewardsCount);
        $status->setPenaltiesCount($penaltiesCount);
        $this->partnerStatusCommandRepository->updateCountsOnly($status);
    }

    private function createStatus(
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
