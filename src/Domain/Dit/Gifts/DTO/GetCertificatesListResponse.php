<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\DTO;

use App\Domain\Dit\Gifts\Entity\Certificate;
use Illuminate\Support\Enumerable;

class GetCertificatesListResponse
{
    private function __construct(
        public readonly array $items,
        public readonly int $total
    ) {
    }

    /**
     * @param Enumerable<int, Certificate> $certificates
     */
    public static function build(Enumerable $certificates): self
    {
        $items = $certificates
            ->map(fn (Certificate $certificate): array => [
                ...$certificate->toArray(),
            ])
            ->values()
            ->all();

        return new self(
            $items,
            $certificates->getTotal()
        );
    }
}
