<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\Shop;
use App\Domain\Marketing\AdventCalendar\Repository\GetShopsQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetShopsUseCase
{
    public function __construct(
        private GetShopsQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, Shop>
     */
    public function getData(?string $lang): Enumerable
    {
        return $this->repository->getListOfShops($lang);
    }
}
