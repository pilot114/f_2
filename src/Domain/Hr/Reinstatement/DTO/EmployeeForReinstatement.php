<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\DTO;

use App\Domain\Hr\Reinstatement\Entity\Employee;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

class EmployeeForReinstatement
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $quitDate,
        public ?string $quitDateForSorting,
        public string $department,
        public string $email,
        public string $login,
    ) {
    }

    /**
     * @param Enumerable<int, Employee> $entity
     */
    public static function arrayFromEntity(Enumerable $entity): array
    {
        $result = [];
        $entity->toArray();
        foreach ($entity as $item) {
            $result[] = new EmployeeForReinstatement(
                id: $item->id,
                name: $item->name,
                quitDate: $item->quitDate instanceof DateTimeImmutable ? $item->quitDate->format('d.m.Y') : null,
                quitDateForSorting: $item->quitDate instanceof DateTimeImmutable ? $item->quitDate->format('Y.m.d') : null,
                department: $item->department,
                email: $item->email,
                login: $item->login,
            );
        }
        return $result;
    }

}
