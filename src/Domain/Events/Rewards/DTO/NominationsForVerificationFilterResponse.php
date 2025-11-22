<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Nomination;
use Illuminate\Support\Enumerable;

class NominationsForVerificationFilterResponse
{
    private function __construct(
        public array $items
    ) {
    }

    /**
     * @param Enumerable<int, Nomination> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Nomination $nomination): array => [
                'id'   => $nomination->id,
                'name' => $nomination->name . ' (' . $nomination->getProgram()->name . ')',
            ])
            ->values()
            ->all();

        return new self($items);
    }
}
