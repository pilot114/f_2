<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\DTO;

use App\Common\Attribute\RpcParam;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeCertificateBalanceRequest
{
    public function __construct(
        #[RpcParam('сумма списания')]
        #[Assert\GreaterThanOrEqual(1)]
        public readonly float|int $amount,
        #[RpcParam('Контракт из сертификата')]
        public readonly string $contract,
        #[RpcParam('Номер сертификата')]
        public readonly string $certificateNumber,
        #[RpcParam('Комментарий')]
        public readonly ?string $commentary = null,
    ) {
    }
}
