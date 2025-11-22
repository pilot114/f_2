<?php

declare(strict_types=1);

namespace App\Domain\Finance\SelfEmployed\Entity;

use Database\ORM\Attribute\Entity;

#[Entity(name: ReportData::PROCEDURE)]
class ReportData
{
    public const PROCEDURE = 'NET.PCURSORS.EXCEEDING_LIMIT_SELF_EMPLOYED_REP';

}
