<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.kpi_metric')]
class KpiMetric
{
    public function __construct(
        #[Column] public readonly int                                    $id,
        #[Column] protected string                                       $name,
        #[Column(name: 'kpi_type')] protected KpiType                    $kpiType,
        #[Column(name: 'calculation_type')] protected KpiCalculationType $calculationType,
        #[Column(name: 'calculation_description')] protected string      $calculationTypeDescription,
        #[Column(name: 'unit_type_id')] protected UnitType               $unitType,
        #[Column] protected KpiMetricGroup                               $group,
        #[Column] protected KpiMetricType                                $type,
        /** @var array<int, CpDepartment> */
        #[Column(collectionOf: CpDepartment::class)] protected array     $departments = [],
        #[Column(name: 'is_active')] protected bool                      $isActive = true,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addMetricDepartment(CpDepartment $department, Post $post): self
    {
        $depId = $department->getId();
        if (!isset($this->departments[$depId])) {
            $this->departments[$depId] = $department;
        }
        $this->departments[$depId]->addPost($post);
        return $this;
    }

    public function removeMetricDepartment(int $departmentId, int $postId): self
    {
        if (isset($this->departments[$departmentId])) {
            $this->departments[$departmentId]->removePost($postId);
            if ($this->departments[$departmentId]->getPosts() === []) {
                unset($this->departments[$departmentId]);
            }
        }
        return $this;
    }

    public function setGroup(KpiMetricGroup $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function setType(KpiMetricType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setKpiType(KpiType $kpiType): self
    {
        $this->kpiType = $kpiType;
        return $this;
    }

    public function setCalculationType(KpiCalculationType $calculationType): self
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    public function setCalculationTypeDescription(string $calculationTypeDescription): self
    {
        $this->calculationTypeDescription = $calculationTypeDescription;
        return $this;
    }

    public function setUnitType(UnitType $unitType): self
    {
        $this->unitType = $unitType;
        return $this;
    }

    public function setIsActive(bool $isActive): KpiMetric
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function toArray(bool $isExtended = true): array
    {
        if ($isExtended === false) {
            return [
                'id'   => $this->id,
                'name' => $this->name,
            ];
        }

        $departmentsWithPosts = [];
        foreach ($this->departments as $department) {
            foreach ($department->getPosts() as $post) {
                $departmentsWithPosts[] = [
                    'departmentId'   => $department->getId(),
                    'departmentName' => $department->getName(),
                    'postId'         => $post->getId(),
                    'postName'       => $post->getName(),
                ];
            }
        }

        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'kpiType'                    => $this->kpiType,
            'calculationType'            => $this->calculationType,
            'calculationTypeDescription' => $this->calculationTypeDescription,
            'group'                      => $this->group->toArray(),
            'type'                       => $this->type->toArray(),
            'departments'                => $departmentsWithPosts,
            'unitType'                   => $this->unitType,
        ];
    }
}
