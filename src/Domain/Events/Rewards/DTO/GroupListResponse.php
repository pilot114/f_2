<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Group;
use Illuminate\Support\Enumerable;

class GroupListResponse
{
    private function __construct(
        /** @var GroupWithProgramsResponse[] $items */
        public array $items,
        public int $rewardsTotal,
        public int $total,
    ) {
    }

    /**
     * @param Enumerable<int, Group> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $items = $entities
            ->map(fn (Group $group): GroupWithProgramsResponse => $group->toGroupWithProgramsResponse())
            ->values()
            ->all();

        $rewardsTotal = 0;

        /** @var GroupWithProgramsResponse $group */
        foreach ($items as $group) {
            foreach ($group->programs as $program) {
                foreach ($program->nominations as $nomination) {
                    $rewardsTotal += $nomination->rewards_count;
                }
            }
        }

        return new self(
            $items,
            $rewardsTotal,
            $entities->getTotal(),
        );
    }
}
