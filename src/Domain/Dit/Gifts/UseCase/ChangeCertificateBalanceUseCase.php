<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Repository\CertificateCommandRepository;
use App\Domain\Dit\Gifts\Repository\CertificatesQueryRepository;

class ChangeCertificateBalanceUseCase
{
    public function __construct(
        private CertificatesQueryRepository $queryRepository,
        private CertificateCommandRepository $commandRepository
    ) {
    }

    public function writeOff(float|int $amount, string $contract, string $certificateNumber, ?string $commentary): Certificate
    {
        $amount = (float) $amount;
        $certificate = $this->getCertificate($contract, $certificateNumber);

        if ($certificate->sumRemains < $amount) {
            throw new InvariantDomainException('Списание невозможно. Остаток меньше суммы списания');
        }

        $this->commandRepository->writeOff($amount, $contract, $certificateNumber, $commentary);

        return $this->getCertificate($contract, $certificateNumber);
    }

    public function addSum(float|int $amount, string $contract, string $certificateNumber, ?string $commentary): Certificate
    {
        $amount = (float) $amount;
        $certificate = $this->getCertificate($contract, $certificateNumber);

        if (!$certificate->isRealWriteOffOperationExists()) {
            throw new InvariantDomainException('Начисление невозможно. По сертификату нет операций реального списания');
        }

        if ($certificate->isAutoWriteOffOperationExists()) {
            throw new InvariantDomainException('Начисление невозможно. Перед начислением нужно удалить все операции автосписания');
        }

        $newDenomination = $certificate->calculateNewDenomination($amount);
        $this->commandRepository->addSum($newDenomination, $contract, $certificateNumber, $commentary);

        return $this->getCertificate($contract, $certificateNumber);

    }

    public function cancelAutoWriteOff(string $contract, string $certificateNumber): Certificate
    {
        $certificate = $this->getCertificate($contract, $certificateNumber);

        if (!$certificate->isAutoWriteOffOperationExists()) {
            throw new InvariantDomainException('не найдены операции автосписания');
        }

        $this->commandRepository->cancelWriteOff($contract, $certificateNumber);

        return $this->getCertificate($contract, $certificateNumber);
    }

    private function getCertificate(string $contract, string $certificateNumber): Certificate
    {
        $certificate = $this->queryRepository->getCertificate($contract, $certificateNumber);
        $usages = $this->queryRepository->getCertificatesUsages([$certificate->number]);
        $certificate->setUsages($usages);

        return $certificate;
    }
}
