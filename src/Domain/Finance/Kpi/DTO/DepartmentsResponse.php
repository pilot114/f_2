<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use Illuminate\Support\Enumerable;

readonly class DepartmentsResponse
{
    private function __construct(
        public array   $items,
        public int     $total,
    ) {
    }

    /**
     * @param Enumerable<int, CpDepartment> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(function (CpDepartment $x): array {
                $dep = $x->toArray();
                return [
                    'id'   => $dep['id'],
                    'name' => trim($dep['name']),
                ];
            })
            ->values()
            ->all()
        ;

        return new self(
            $items,
            $entities->getTotal(),
        );
    }
}
