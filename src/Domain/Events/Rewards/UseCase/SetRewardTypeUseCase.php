<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\SetRewardTypeRequest;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Repository\RewardCommandRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use Database\ORM\QueryRepositoryInterface;

class SetRewardTypeUseCase
{
    public function __construct(
        private RewardQueryRepository $rewardQueryRepository,
        /** @var QueryRepositoryInterface<RewardType> $rewardTypeQueryRepository */
        private QueryRepositoryInterface $rewardTypeQueryRepository,
        private RewardCommandRepository $rewardCommandRepository
    ) {
    }

    public function setRewardType(SetRewardTypeRequest $request): Reward
    {
        $reward = $this->rewardQueryRepository->getOne($request->rewardId);

        if (is_null($request->typeId)) {
            $reward->setRewardType(null);
            $this->rewardCommandRepository->deleteRewardType($reward->productId);
            return $reward;
        }

        $rewardType = $this->rewardTypeQueryRepository->findOrFail($request->typeId, "Не найден тип награды с id = " . $request->typeId);

        if ($reward->getRewardType() instanceof RewardType) {
            $reward->setRewardType($rewardType);
            $this->rewardCommandRepository->changeRewardType($reward->productId, $rewardType->id);
        } else {
            $reward->setRewardType($rewardType);
            $this->rewardCommandRepository->addRewardType($reward->productId, $rewardType->id);
        }

        return $reward;
    }
}
