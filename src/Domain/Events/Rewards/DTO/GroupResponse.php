<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\Entity\Group;

class GroupResponse
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }

    public static function build(Group $group): self
    {
        return new self($group->id, $group->getName());
    }
}
