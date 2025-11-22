<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Dit\Gifts\UseCase\DeleteCertificateUseCase;

class DeleteCertificateController
{
    public function __construct(
        private DeleteCertificateUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'dit.gifts.deleteCertificate',
        'удаление сертификата',
        examples: [
            [
                'summary' => 'удаление сертификата',
                'params'  => [
                    'contract'          => '"12123"',
                    'certificateNumber' => '"12312341"',
                ],
            ],
        ],
    )]
    #[CpMenu('certificate-management')]
    public function delete(
        #[RpcParam('Контракт из сертификата')]
        string $contract,
        #[RpcParam('Номер сертификата')]
        string $certificateNumber,
    ): bool {
        return $this->useCase->delete($contract, $certificateNumber);
    }
}
