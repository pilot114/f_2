<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Repository;

use App\Domain\Dit\Gifts\Entity\Certificate;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<Certificate> */
class CertificateCommandRepository extends CommandRepository
{
    protected string $entityName = Certificate::class;

    public function writeOff(float $amount, string $contract, string $certificateNumber, ?string $commentary): void
    {
        $this->conn->procedure('tehno.sertificat_tools.write_off_sertificat_summ', [
            'pContract'    => $contract,
            'p_numsert'    => $certificateNumber,
            'p_Summ'       => $amount,
            'p_Commentary' => $commentary,
        ]);
    }

    public function addSum(float $amount, string $contract, string $certificateNumber, ?string $commentary): void
    {
        $this->conn->procedure('tehno.sertificat_tools.add_summ_to_sertificat', [
            'pContract'    => $contract,
            'p_numsert'    => $certificateNumber,
            'p_Summ'       => $amount,
            'p_Commentary' => $commentary,
        ]);
    }

    public function cancelWriteOff(string $contract, string $certificateNumber): void
    {
        $this->conn->procedure('tehno.sertificat_tools.cancel_write_off_sertificat', [
            'pContract' => $contract,
            'p_numsert' => $certificateNumber,
        ]);
    }

    public function deleteCertificate(string $contract, string $certificateNumber): void
    {
        $this->conn->procedure('tehno.sertificat_tools.delete_sertificat', [
            'pContract' => $contract,
            'p_numsert' => $certificateNumber,
        ]);
    }
}
