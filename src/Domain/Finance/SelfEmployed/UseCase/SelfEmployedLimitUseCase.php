<?php

declare(strict_types=1);

namespace App\Domain\Finance\SelfEmployed\UseCase;

use App\Domain\Finance\SelfEmployed\Repository\SelfEmployedLimitRepository;
use DateTimeImmutable;

class SelfEmployedLimitUseCase
{
    public function __construct(
        private SelfEmployedLimitRepository $repository,
    ) {
    }

    public function getReport(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTill): array
    {
        return $this->repository->getReport($dateFrom, $dateTill);
    }
}
