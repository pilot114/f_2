<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use DateTimeInterface;
use Illuminate\Support\Enumerable;

readonly class ListResponse
{
    private function __construct(
        public array   $items,
        public int     $total,
        public ?string $lastDateSend,
        public bool    $currentUserIsDepartmentBoss,
    ) {
    }

    /**
     * @param Enumerable<int, KpiDepartment> $entities
     */
    public static function build(Enumerable $entities, ?DateTimeInterface $lastDateSend, bool $currentUserIsDepartmentBoss): self
    {
        $items = $entities
            ->map(fn (KpiDepartment $x): array => $x->toArray())
            ->all();

        // countActualEmptyKpi должен учитывать дочерние департаменты
        // ! чтобы это работало, нужно чтобы dep.level был отсортирован от большего к меньшему
        foreach ($items as $item) {
            if (isset($items[$item['parentId']])) {
                $items[$item['parentId']]['countActualEmptyKpi'] += $item['countActualEmptyKpi'];
            }
        }
        $items = array_values($items);

        return new self(
            $items,
            $entities->getTotal(),
            $lastDateSend?->format(DateTimeInterface::ATOM),
            $currentUserIsDepartmentBoss
        );
    }
}
