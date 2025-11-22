<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\DTO;

use App\Domain\Finance\PointsLoan\Entity\Partner;

class GetPartnerStatsResponse
{
    private function __construct(
        public readonly int $id,
        public readonly string $contract,
        public readonly string $name,
        public readonly ?string $email,
        public readonly array $country,
        public readonly int $monthInCompany,
        public readonly ?array $violation,
        public readonly bool $isActive,
        public readonly array $stats,
    ) {
    }

    public static function build(Partner $partner): self
    {
        return new self(
            ...$partner->toArray(),
        );
    }
}
