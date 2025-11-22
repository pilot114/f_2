<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Dit\Gifts\DTO\ChangeCertificateBalanceRequest;
use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\UseCase\ChangeCertificateBalanceUseCase;

class ChangeCertificateBalanceController
{
    public function __construct(
        private ChangeCertificateBalanceUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'dit.gifts.writeOffCertificate',
        'списание с сертификата',
        examples: [
            [
                'summary' => 'списание с сертификата',
                'params'  => [
                    'amount'            => 100.00,
                    'contract'          => '"12123"',
                    'certificateNumber' => '"12312341"',
                    'commentary'        => 'comment text',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('certificate-management')]
    public function writeOff(
        ChangeCertificateBalanceRequest $request
    ): Certificate {
        return $this->useCase->writeOff((float) $request->amount, $request->contract, $request->certificateNumber, $request->commentary);
    }

    #[RpcMethod(
        'dit.gifts.addSumToCertificate',
        'пополнение сертификата',
        examples: [
            [
                'summary' => 'пополнение сертификата',
                'params'  => [
                    'amount'            => 100.00,
                    'contract'          => '"12123"',
                    'certificateNumber' => '"12312341"',
                    'commentary'        => 'added 100',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('certificate-management')]
    public function addSum(
        ChangeCertificateBalanceRequest $request
    ): Certificate {
        return $this->useCase->addSum((float) $request->amount, $request->contract, $request->certificateNumber, $request->commentary);
    }

    #[RpcMethod(
        'dit.gifts.cancelAutoWriteOff',
        'отмена всех автосписаний',
        examples: [
            [
                'summary' => 'отмена всех автосписаний',
                'params'  => [
                    'contract'          => '"12123"',
                    'certificateNumber' => '"12312341"',
                ],
            ],
        ],
    )]
    #[CpMenu('certificate-management')]
    public function cancelAutoWriteOff(
        #[RpcParam('Контракт из сертификата')]
        string $contract,
        #[RpcParam('Номер сертификата')]
        string $certificateNumber,
    ): Certificate {
        return $this->useCase->cancelAutoWriteOff($contract, $certificateNumber);
    }
}
