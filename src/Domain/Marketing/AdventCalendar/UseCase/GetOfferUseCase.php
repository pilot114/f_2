<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Entity\Offer;
use App\Domain\Marketing\AdventCalendar\Repository\GetOfferQueryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class GetOfferUseCase
{
    public function __construct(
        private GetOfferQueryRepository $repository,
    ) {
    }

    /**
     * Получение данных адвента по магазину.
     */
    public function getData(int $id): Offer
    {
        $result = $this->repository->getData($id);

        if (!$result instanceof Offer) {
            throw new NotFoundHttpException("Предложение $id не найдено");
        }

        return $result;
    }
}
