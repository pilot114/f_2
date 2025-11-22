<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Cabinet\Entity\User;
use Illuminate\Support\Enumerable;

readonly class GetUsersListResponse
{
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }

    /** @param Enumerable<int, User> $users*/
    public static function build(Enumerable $users): self
    {
        {
            $items = $users
                ->map(fn (User $user): array => [
                    ...$user->toArray(),
                ])
                ->values()
                ->all();

            return new self(
                $items,
                $users->getTotal()
            );
        }
    }
}
