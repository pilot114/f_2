<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\DTO;

use App\Domain\Hr\MemoryPages\Entity\Employee;
use Illuminate\Support\Enumerable;

class GetEmployeeListResponse
{
    private function __construct(
        public array $items,
        public int $total,
    ) {
    }

    /**
     * @param Enumerable<int, Employee> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Employee $employee): array => [
                ...$employee->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $entities->getTotal()
        );
    }
}
