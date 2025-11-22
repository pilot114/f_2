<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use App\Domain\Marketing\CustomerHistory\Enum\Status;
use App\Domain\Marketing\CustomerHistory\Repository\CustomerHistoryQueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

readonly class GetCustomerHistoryUseCase
{
    public function __construct(
        private CustomerHistoryQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, HistoryItem>
     */
    public function getData(
        ?string $q,
        ?Status $state,
        ?string $lang,
        ?DateTimeImmutable    $dateFrom,
        ?DateTimeImmutable $dateTill,
        int $page,
        int $perPage,
    ): Enumerable {
        return $this->repository->getData(
            q: $q,
            state: $state,
            lang: $lang,
            dateFrom: $dateFrom,
            dateTill: $dateTill,
            page: $page,
            perPage: $perPage,
        );
    }
}
