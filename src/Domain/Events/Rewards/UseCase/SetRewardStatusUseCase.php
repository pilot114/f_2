<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Enum\RewardStatusType;
use App\Domain\Events\Rewards\Repository\CountryQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\Repository\StatusCommandRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SetRewardStatusUseCase
{
    public function __construct(
        private RewardQueryRepository $readRewards,
        private CountryQueryRepository $readCountry,
        private StatusCommandRepository $writeStatus,
        private SecurityUser $currentUser,
        private TransactionInterface $transaction,
    ) {
    }

    public function setRewardStatus(int $rewardId, array $active, array $archive): void
    {
        if ($intersect = array_intersect($active, $archive)) {
            throw new ConflictHttpException(
                "Нельзя задать два статуса одновременно. Id стран с пересекающимися статусами: " . implode(', ', $intersect),
            );
        }

        $reward = $this->readRewards->getOne($rewardId);
        $activeInCountries = $this->readCountry->getByIds($active);
        $archiveInCountries = $this->readCountry->getByIds($archive);

        $this->transaction->beginTransaction();
        foreach ($activeInCountries as $activeInCountry) {
            $active = new RewardStatus(Loader::ID_FOR_INSERT, RewardStatusType::ACTIVE, $activeInCountry);
            $this->process($reward, $active, $activeInCountry);
        }
        foreach ($archiveInCountries as $archiveInCountry) {
            $archive = new RewardStatus(Loader::ID_FOR_INSERT, RewardStatusType::ARCHIVE, $archiveInCountry);
            $this->process($reward, $archive, $archiveInCountry);
        }
        $this->transaction->commit();
    }

    private function process(Reward $reward, RewardStatus $newStatus, Country $country): void
    {
        $existingStatusInCountry = $reward->findStatusInCountry($country);

        if ($existingStatusInCountry && $existingStatusInCountry->getStatusId() === $newStatus->getStatusId()) {
            return;
        }

        $id = (int) $this->currentUser->getUserIdentifier();

        $existingStatusInCountry instanceof RewardStatus
            ? $this->writeStatus->updateStatusInCountry($reward, $newStatus, $id)
            : $this->writeStatus->createStatusInCountry($reward, $newStatus, $id)
        ;
    }
}
