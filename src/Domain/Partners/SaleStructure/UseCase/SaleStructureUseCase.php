<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\UseCase;

use App\Common\Helper\DateHelper;
use App\Domain\Partners\SaleStructure\Entity\PartnerSaleStructure;
use App\Domain\Partners\SaleStructure\Repository\SaleStructureRepository;
use DateTimeImmutable;

class SaleStructureUseCase
{
    public function __construct(
        private SaleStructureRepository $readRepository,
    ) {
    }

    public function get(string $contract, DateTimeImmutable $from, DateTimeImmutable $till): array
    {
        $data = $this->readRepository->getSaleStructure($contract, $from, $till);
        $grouped = [];
        $countryCodes = [];
        $mockMonth = [];
        foreach ($data as $item) {
            $date = new DateHelper($item['dt']);
            $dateFormated = $date->getRussianMonthAndYear();
            if (!isset($grouped[$dateFormated])) {
                $grouped[$dateFormated] = [];
            }

            $countryName = $item['country'];
            // Знай, неизвестный мне менеджер, ты выйграл. Я даже не буду выйснять, в чём тут дело, просто оставлю костыль.
            if ($countryName === 'ТАЙЛАНД') {
                $countryName = 'ТАИЛАНД';
            }
            if (empty($countryCodes[$countryName])) {
                $countryCodes[$countryName] = $this->readRepository->getCountryCode($countryName);
            }

            $dataPart = PartnerSaleStructure::fromDirtyValues(
                id: $countryCodes[$countryName],
                name: $countryName,
                currency: $item['currency'],
                percent: (float) $item['oo_percent'],
                points: (float) $item['oo'],
            );
            $grouped[$dateFormated][$dataPart->getHashKey()] = $dataPart;

            if (!isset($mockMonth[$dataPart->getHashKey()])) {
                $mockMonth[$dataPart->getHashKey()] = PartnerSaleStructure::fromDirtyValues(
                    id: $countryCodes[$countryName],
                    name: $countryName,
                    currency: $item['currency'],
                    percent: 0,
                    points: 0,
                );
            }
        }

        //Обогащение данных для случая когда в некоторых месяцах стран меньше
        foreach ($grouped as &$month) {
            foreach ($mockMonth as $mockData) {
                if (!isset($month[$mockData->getHashKey()])) {
                    $month[$mockData->getHashKey()] = $mockData;
                }
            }
            uasort($month, function ($a, $b): int {
                $result = $a->getName() <=> $b->getName();
                if ($result === 0) {
                    return $b->getCurrency() <=> $a->getCurrency();
                }
                return $result;
            });
        }

        $final = [];
        foreach ($grouped as $period => $item) {
            $final[] = [
                'period'    => $period,
                'countries' => array_values($item),
            ];
        }
        return array_reverse($final);
    }

}
