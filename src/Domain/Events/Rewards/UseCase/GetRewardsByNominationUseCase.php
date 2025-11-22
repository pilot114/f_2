<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetRewardsByNominationUseCase
{
    public function __construct(
        private RewardQueryRepository $rewardQueryRepository
    ) {
    }

    public function getNominationWithRewards(int $rewardId, int $countryId): Nomination
    {
        $rewards = $this->rewardQueryRepository->getRewardsInNomination($rewardId, $countryId);
        $nomination = $rewards->first()?->getNomination();
        if (null === $nomination) {
            throw new NotFoundHttpException('Не найдена номинация в которой есть награда с id ' . $rewardId);
        }
        $nomination->setRewards($rewards->toArray());

        return $nomination;
    }
}
