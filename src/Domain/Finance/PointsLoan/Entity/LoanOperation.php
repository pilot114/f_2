<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'NET.NEWSPIS', sequenceName: 'NET.SQNEWSPIS')]
class LoanOperation
{
    public function __construct(
        #[Column] private int                       $id,
        #[Column] public readonly DateTimeImmutable $ds,
        #[Column] public readonly DateTimeImmutable $de,
        #[Column] public readonly int               $emp_spis,
        #[Column] public readonly int               $emp_nach,
        #[Column] public readonly int                $emp_buy,
        #[Column] public readonly int               $spistype,
        #[Column] private float                            $lo,
        #[Column] public readonly string                 $prim,
        #[Column] public readonly int                    $curr,
        #[Column] public readonly float                   $sum,
        #[Column] public readonly int                   $kommb,
        #[Column] public readonly float            $sum_native,
        #[Column] public readonly float          $kommb_native,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function update(float $sum): void
    {
        $this->lo = $sum;
    }
}
