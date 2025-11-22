<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\BackgroundImage;
use App\Domain\Marketing\AdventCalendar\Repository\GetBackgroundImagesQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetBackgroundImagesUseCase
{
    public function __construct(
        private GetBackgroundImagesQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, BackgroundImage>
     */
    public function getData(): Enumerable
    {
        return $this->repository->getListOfBackgroundImages();
    }
}
