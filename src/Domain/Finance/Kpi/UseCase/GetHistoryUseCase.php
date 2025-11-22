<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleEnterprise;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleUser;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

class GetHistoryUseCase
{
    public function __construct(
        private KpiQueryRepository $read,
        private KpiResponsibleQueryRepository $readResponsible,
    ) {
    }

    /**
     * @return array{0: Enumerable<int, Kpi>, 1: KpiResponsible}
     */
    public function getHistory(int $empId): array
    {
        $kpis = $this->read->getHistory($empId);
        $responsible = $this->readResponsible->getActualResponsible($empId);

        // ответственный по умолчанию
        if (!$responsible instanceof KpiResponsible) {
            $responsible = new KpiResponsible(
                id: 1,
                user: new KpiResponsibleUser(
                    id: 247,
                    name: 'Загорулина Инна Васильевна',
                    responseName: 'Главный бухгалтер',
                ),
                enterprise: new KpiResponsibleEnterprise(
                    id: 117933714801,
                    name: 'Международная компания Сибирское здоровье',
                ),
                changeDate: new DateTimeImmutable(),
                changeUserId: 5413,
            );
        }

        return [$kpis, $responsible];
    }
}
