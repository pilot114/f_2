<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

readonly class CokResponse
{
    public function __construct(
        public int                        $id,
        public int                        $departmentId,
        public string                     $contract,
        public string                     $name,
        public ?RegionDirectorResponse    $regionalDirector,
        public ?GrandManagerResponse      $grandManager,
        public ?CalculationStatusResponse $calculationStatus,
        public ?string                    $address,
        public ?string                    $phone,
        public ?string                    $email,
        public ?DdmrpParametersResponse   $ddmrpParameters,
        /** @var CokEmployeesResponse[] */
        public array                      $employees = []
    ) {
    }
}
