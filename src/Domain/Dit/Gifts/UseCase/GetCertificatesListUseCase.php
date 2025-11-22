<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\UseCase;

use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Repository\CertificatesQueryRepository;
use Illuminate\Support\Enumerable;

class GetCertificatesListUseCase
{
    public function __construct(
        private CertificatesQueryRepository $queryRepository
    ) {
    }

    /**
     * @return Enumerable<int, Certificate>
     */
    public function getCertificatesList(string $search): Enumerable
    {
        $certificates = $this->queryRepository->getCertificatesList($search);
        $usages = $this->queryRepository->getCertificatesUsages($certificates->pluck('number')->values()->toArray());

        $groupedUsages = $usages->groupBy('certificateNumber', true);

        $certificates->each(function (Certificate $certificate) use ($groupedUsages): void {
            $usagesByNumber = $groupedUsages->get($certificate->number);

            if ($usagesByNumber instanceof Enumerable) {
                $certificate->setUsages($usagesByNumber);
            }
        });

        return $certificates;
    }
}
