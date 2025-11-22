<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnersByContractsRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Repository\PartnersByContractQueryRepository;
use Illuminate\Support\Enumerable;

class GetPartnersByContractsUseCase
{
    public function __construct(
        private PartnersByContractQueryRepository $repository
    ) {
    }

    /**
     * @return Enumerable<int, Partner>
     */
    public function getWithActiveRewards(PartnersByContractsRequest $request): Enumerable
    {
        return $this->repository->getWithActiveReward($request);
    }

    /**
     * @return Enumerable<int, Partner>
     */
    public function getWithDeletedRewards(PartnersByContractsRequest $request): Enumerable
    {
        return $this->repository->getWithDeletedRewards($request);
    }
}
