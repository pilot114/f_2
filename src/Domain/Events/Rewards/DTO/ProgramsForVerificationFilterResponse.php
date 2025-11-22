<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Program;
use Illuminate\Support\Enumerable;

class ProgramsForVerificationFilterResponse
{
    private function __construct(
        public array $items
    ) {
    }

    /**
     * @param Enumerable<int, Program> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Program $program): array => [
                'id'   => $program->id,
                'name' => $program->name,
            ])
            ->values()
            ->all();

        return new self($items);
    }
}
