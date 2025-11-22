<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Common\Helper\EnumerableWithTotal;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use Generator;

#[Entity('test.cp_departament')]
class KpiDepartment
{
    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name,
        #[Column(name: 'idparent')] private int $parentId,
        #[Column(name: 'dep_level')] private int $level,
        /** @var array<int, KpiEmployee> */
        #[Column(collectionOf: KpiEmployee::class)] private array $emps = [],
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return Generator<Kpi>
     */
    public function getKpi(): Generator
    {
        foreach ($this->emps as $emp) {
            foreach ($emp->getKpi() as $kpi) {
                yield $kpi;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'parentId'            => $this->parentId,
            'level'               => $this->level,
            'emps'                => $this->getEmps(),
            'countActualEmptyKpi' => $this->countActualEmptyKpi(),
        ];
    }

    private function getEmps(): array
    {
        return array_values(array_map(fn (KpiEmployee $emp): array => [
            ...$emp->toArray(),
            ...$emp->getUserPics(),
            ...$emp->getActualKpiEachType(),
            ...$emp->getMainPosition(),
        ], $this->emps));
    }

    private function countActualEmptyKpi(): int
    {
        $result = EnumerableWithTotal::build($this->emps)
            ->map(fn (KpiEmployee $x): int => $x->countActualEmptyKpi())
            ->sum();
        return is_int($result) ? $result : 0;
    }
}
