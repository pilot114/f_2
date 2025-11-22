<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class SetDdmrpParametersRequest
{
    public function __construct(
        #[Assert\Valid]
        public DdmrpParameters $ddmrpParameters,
        #[Assert\NotBlank(message: 'Контракт не может быть пустым')]
        public string $contract,
    ) {
    }
}
