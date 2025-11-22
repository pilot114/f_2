<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\UseCase;

use App\Domain\Finance\PointsLoan\Entity\Partner;
use App\Domain\Finance\PointsLoan\Repository\PartnerQueryRepository;

class GetPartnerStatsUseCase
{
    public function __construct(
        private PartnerQueryRepository $queryRepository
    ) {
    }

    public function getPartnerStats(string $contract): Partner
    {
        $partner = $this->queryRepository->getPartnerStats($contract);
        $emails = $this->queryRepository->getPartnerEmails($partner->id);
        $partner->addEmails($emails);

        return $partner;
    }
}
