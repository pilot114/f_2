<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class GroupWithProgramsResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $programs_count,
        /** @var ProgramWithNominationsResponse[] $programs */
        public readonly array $programs
    ) {
    }
}
