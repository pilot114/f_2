<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Repository\WriteProductCommandRepository;
use Database\Connection\TransactionInterface;
use DomainException;
use Throwable;

readonly class SaveProductUseCase
{
    public function __construct(
        private WriteProductCommandRepository $repository,
        private TransactionInterface $transaction,
    ) {
    }

    /**
     * @throws \App\Common\Exception\DomainException
     */
    public function saveProduct(
        int $calendarId,
        array $codes,
    ): true {

        $this->transaction->beginTransaction();
        try {
            foreach ($codes as $code) {
                $this->repository->addProductOfMonth($calendarId, $code);
            }
        } catch (Throwable $e) {
            throw new DomainException("Календарь или продукт не существует", 400, $e);
        }
        $this->transaction->commit();

        return true;
    }

    public function removeProduct(
        array $productIds,
    ): true {

        $this->transaction->beginTransaction();

        foreach ($productIds as $id) {
            $this->repository->deleteProductOfMonth($id);
        }

        $this->transaction->commit();

        return true;
    }
}
