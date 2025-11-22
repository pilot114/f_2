<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\DTO\CreateMonthRequest;
use App\Domain\Marketing\AdventCalendar\DTO\SaveMonthParamsRequest;
use App\Domain\Marketing\AdventCalendar\Repository\WriteMonthCommandRepository;
use Database\Connection\TransactionInterface;

readonly class SaveMonthParamsUseCase
{
    public function __construct(
        private WriteMonthCommandRepository $repository,
        private TransactionInterface $transaction,
    ) {
    }

    public function createMonth(
        CreateMonthRequest $request
    ): int {
        return $this->repository->addPeriodToCalendar($request->year, $request->month, $request->shop);
    }

    public function saveMonthParams(
        SaveMonthParamsRequest $request
    ): true {

        $this->transaction->beginTransaction();

        foreach ($request->langs as $lang) {
            $this->repository->updatePeriodLang($request->calendarId, $lang->lang, $lang->title, $lang->label);
        }

        $this->transaction->commit();

        return true;
    }
}
