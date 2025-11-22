<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnersByEventRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\Registration;
use App\Domain\Events\Rewards\Repository\PartnersByEventQueryRepository;
use Illuminate\Support\Enumerable;

class GetPartnersByEventUseCase
{
    public function __construct(
        private PartnersByEventQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, Partner>
     */
    public function getList(PartnersByEventRequest $request): Enumerable
    {
        $partners = $this->repository->getPartnersByEvent($request);
        $registrations = $this->repository->getPartnersRegistrations($partners->pluck('id')->toArray(), $request->eventId);

        $registrationsByPartnerId = $registrations->groupBy('partnerId');

        return $partners->map(function (Partner $partner) use ($registrationsByPartnerId): Partner {
            $partner->getStatus()?->setRewardsCount($partner->getFilteredRewardsCount());
            /** @var Enumerable<int, Registration> $registrationsCollection */
            $registrationsCollection = $registrationsByPartnerId->get($partner->id, collect());
            $partner->addRegistrations($registrationsCollection->toArray());

            return $partner;
        });
    }

    public function count(PartnersByEventRequest $request): int
    {
        return $this->repository->countPartnersByEvent($request);
    }
}
