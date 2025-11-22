<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Repository\PartnersByEventQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;

class GetPartnerFullInfoUseCase
{
    public function __construct(
        private PartnersFullInfoQueryRepository $repository,
        private PartnersByEventQueryRepository $byEventQueryRepository,
    ) {
    }

    public function getPartnerFullInfo(PartnerFullInfoRequest $request): Partner
    {
        if ($request->hideDeletedRewards === false) {
            $partner = $this->repository->getWithDeletedRewards($request);
        } else {
            $partner = $this->repository->getWithActiveRewards($request);
        }

        if (!is_null($request->eventId)) {
            $registrations = $this->byEventQueryRepository->getPartnersRegistrations([$partner->id], $request->eventId);
            $partner->addRegistrations($registrations->values()->toArray());
        }

        return $partner;
    }
}
