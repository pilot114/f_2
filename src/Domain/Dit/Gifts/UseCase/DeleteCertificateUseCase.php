<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Repository\CertificateCommandRepository;
use App\Domain\Dit\Gifts\Repository\CertificatesQueryRepository;

class DeleteCertificateUseCase
{
    public function __construct(
        private CertificatesQueryRepository $queryRepository,
        private CertificateCommandRepository $commandRepository
    ) {
    }

    public function delete(string $contract, string $certificateNumber): bool
    {
        $certificate = $this->getCertificate($contract, $certificateNumber);

        if (!$certificate->canBeDeleted()) {
            throw new InvariantDomainException('возможно удаление сертификата только если не было начислений/списаний');
        }

        $this->commandRepository->deleteCertificate($contract, $certificateNumber);

        return true;
    }

    private function getCertificate(string $contract, string $certificateNumber): Certificate
    {
        $certificate = $this->queryRepository->getCertificate($contract, $certificateNumber);
        $usages = $this->queryRepository->getCertificatesUsages([$certificate->number]);
        $certificate->setUsages($usages);

        return $certificate;
    }
}
