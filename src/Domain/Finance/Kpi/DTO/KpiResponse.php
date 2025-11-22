<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Enum\KpiType;

readonly class KpiResponse
{
    private function __construct(
        public int     $id,
        public int     $empId,
        public string  $billingMonth,
        public KpiType $type,
        public string  $periodTitle,
        public ?int    $value,
        public string  $valueTitle,
        public bool    $isSent,
        public ?string $sendDate,
        public bool    $isActual,
        public array   $metrics,
    ) {
    }

    public static function build(Kpi $kpi, int $empId): self
    {
        $data = [
            ...$kpi->toArray($empId),
            'metrics' => $kpi->getMetricHistory(),
        ];
        return new self(...$data);
    }
}
