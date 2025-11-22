<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Common\Exception\ProductImageException;
use App\Domain\Marketing\AdventCalendar\Entity\AdventItem;
use App\Domain\Marketing\AdventCalendar\Repository\GetAdventCalendarQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetAdventCalendarUseCase
{
    public function __construct(
        private GetAdventCalendarQueryRepository $repository
    ) {
    }

    /**
     * @return Enumerable<int, AdventItem>
     * @throws ProductImageException
     */
    public function getData(
        ?string $shopId,
    ): Enumerable {
        return $this->repository->getData($shopId);
    }
}
