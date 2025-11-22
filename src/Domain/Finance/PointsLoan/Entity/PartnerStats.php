<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('net.cache_main')]
readonly class PartnerStats
{
    public function __construct(
        #[Column(name: 'id')] public string               $id,
        #[Column(name: 'personal_volume')] public int     $personalVolume,
        #[Column(name: 'total_volume')] public int        $totalVolume,
        #[Column(name: 'rang')] public int                $rang,
        #[Column(name: 'month')] public DateTimeImmutable $month,
    ) {
    }

    public function toArray(): array
    {
        return [
            'month'          => $this->month->format('Y-m-d'),
            'personalVolume' => $this->personalVolume,
            'totalVolume'    => $this->totalVolume,
            'rang'           => $this->rang,
        ];
    }
}
