<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Cabinet\Entity\Profile;

class GetProfileResponse
{
    private function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $passCard,
        public readonly ?string $snils,
        public readonly ?string $birthday,
        public readonly bool $hideBirthday,
        public readonly array $contacts,
        public readonly array $address,
        public readonly ?array $avatar,
        public readonly array $position,
        public readonly array $departmentsHierarchy,
        public readonly ?array $workTime,
    ) {
    }

    public static function build(Profile $profile): self
    {
        return new self(...$profile->toArray());
    }
}
