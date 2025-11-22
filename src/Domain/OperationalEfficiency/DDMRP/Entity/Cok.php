<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\OperationalEfficiency\DDMRP\DTO\CalculationStatusResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\CokEmployeesResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\CokResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParameters as DdmrpParametersDto;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Entity('test.cp_cok_info')]
class Cok
{
    /** @var CokEmployee[] */
    private array $employees = [];

    public function __construct(
        #[Column] private int                                          $id,
        #[Column(name: 'department_id')] public readonly int           $departmentId,
        #[Column] private string                                       $contract,
        #[Column] private string                                       $name,
        #[Column(name: 'regional_director')] private ?RegionalDirector $regionalDirector,
        #[Column(name: 'manager')] private ?GrandManager               $grandManager,
        #[Column(name: 'for_ddmrp_calc')] private ?CalculationStatus   $calculationStatus,
        #[Column] private ?string                                      $address,
        #[Column] private ?string                                      $phone,
        #[Column] private ?string                                      $email,
        #[Column(name: 'ddmrp_parameters')] private DdmrpParameters    $ddmrpParameters,
    ) {
    }

    public function toCokResponse(): CokResponse
    {
        $calculationStatus = $this->getCalculationStatus();

        return new CokResponse(
            id: $this->id,
            departmentId: $this->departmentId,
            contract: $this->contract,
            name: $this->name,
            regionalDirector: $this->regionalDirector?->toRegionDirectorResponse(),
            grandManager: $this->grandManager?->toGrandManagerResponse(),
            calculationStatus: $this->calculationStatus instanceof CalculationStatus
                ? new CalculationStatusResponse($calculationStatus->value,$calculationStatus->getTitle())
                : new CalculationStatusResponse(null, CalculationStatus::NOT_VERIFIED->getTitle()),
            address: $this->address,
            phone: $this->phone,
            email: $this->email,
            ddmrpParameters: $this->ddmrpParameters->toDdmrpParametersResponse(),
            employees: array_map(
                fn (CokEmployee $employee): CokEmployeesResponse => $employee->toCokEmployeesResponse(),
                $this->employees
            )
        );
    }

    public function getCalculationStatus(): CalculationStatus
    {
        return $this->calculationStatus ?? CalculationStatus::NOT_VERIFIED;
    }

    public function addEmployees(array $employees): void
    {
        $this->employees = $employees;
    }

    public function changeCalculationStatus(CalculationStatus $calculationStatus): void
    {
        if ($calculationStatus === CalculationStatus::NOT_VERIFIED) {
            throw new InvariantDomainException('Нельзя перевести ЦОК в "Непроверенные"');
        }

        $this->calculationStatus = $calculationStatus;
    }

    public function canChangeDdmrpParameters(): bool
    {
        if ($this->isExcluded()) {
            throw new InvariantDomainException('Нельзя менять параметры у ЦОКов в статусе "Исключенные"');
        }

        return true;
    }

    public function getDdmrpParameters(): DdmrpParameters
    {
        return $this->ddmrpParameters;
    }

    public function updateDdmrpParameters(DdmrpParametersDto $ddmrpParameters): void
    {
        if ($this->canChangeDdmrpParameters()) {
            $this->ddmrpParameters->update($ddmrpParameters);
        }
    }

    public function canChangeEmployeeAccess(): bool
    {
        if ($this->isExcluded()) {
            throw new InvariantDomainException('Нельзя менять доступ сотрудников для ЦОКов в статусе "Исключенные"');
        }

        return true;
    }

    private function isExcluded(): bool
    {
        return $this->calculationStatus === CalculationStatus::EXCLUDED;
    }

    public function getEmployee(int $employeeId): CokEmployee
    {
        foreach ($this->employees as $employee) {
            if ($employee->getId() === $employeeId) {
                return $employee;
            }
        }

        throw new NotFoundHttpException("сотрудника с id = {$employeeId} нет в Цоке {$this->contract}");
    }
}
