<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\DTO\SaveOfferLanguageRequest;
use App\Domain\Marketing\AdventCalendar\DTO\SaveOfferRequest;
use App\Domain\Marketing\AdventCalendar\Repository\GetOfferQueryRepository;
use App\Domain\Marketing\AdventCalendar\Repository\WriteOfferCommandRepository;
use Database\Connection\TransactionInterface;
use DomainException;
use Illuminate\Support\Collection;

readonly class SaveOfferUseCase
{
    public function __construct(
        private WriteOfferCommandRepository $writeRepository,
        private GetOfferQueryRepository $readRepository,
        private TransactionInterface $transaction,
    ) {
    }

    public function saveOffer(
        SaveOfferRequest $request
    ): int {
        $this->transaction->beginTransaction();

        /** @var Collection<int, SaveOfferLanguageRequest> $langs */
        $langs = collect($request->langs);

        $offerId = $request->offerId;

        if (is_null($offerId)) {
            $result = $this->writeRepository->addOffer(
                $request->calendarId,
                $langs->first()->typeName ?? '',
                $request->active,
                $request->bkImageId
            );
            $offerId = (int) $result['p_Out'];
        } else {
            $this->writeRepository->updateOffer(
                $offerId,
                $request->calendarId,
                $langs->first()->typeName ?? '',
                $request->active,
                $request->bkImageId
            );
        }

        if ($offerId <= 0) {
            throw new DomainException("Не удалось создать предложение");
        }

        // Добавление и/или обновление языковых версий
        foreach ($langs as $lang) {
            $this->writeRepository->updateOfferLang(
                $offerId,
                $lang->lang,
                $lang->typeName,
                $lang->buttonText,
                $lang->shortTitle,
                $lang->shortDescr,
                $lang->shortTitle,
                $lang->fullDescription,
                $lang->imageUrl ?: ($lang->imageId ? $this->readRepository->getOfferImage($lang->imageId) : null),
                $lang->newsLink,
            );
        }

        $this->transaction->commit();

        return $offerId;
    }

    public function removeOffer(
        int $id,
        int $calendarId
    ): true {

        $this->writeRepository->updateOffer(
            $id,
            $calendarId,
            'deleted',
            0,
            1
        );

        return true;
    }
}
