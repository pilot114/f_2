<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\Entity\FinEmployee;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\Service\KpiEmailer;
use App\Domain\Finance\Kpi\Service\KpiExcel;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\ORM\QueryRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class SendToTreasuryUseCase
{
    protected const DEFAULT_EMAIL_WITHOUT_ENTERPRISE = 'sts@sibvaleo.com';  // Сысоева
    protected const DEFAULT_EMAIL_WITHOUT_RESPONSE = 'sh-eco@sibvaleo.com'; // Загорулина

    public function __construct(
        private KpiQueryRepository       $read,
        private KpiCommandRepository     $write,
        private SecurityQueryRepository  $secRepo,
        /** @var QueryRepositoryInterface<FinEmployee> */
        private QueryRepositoryInterface $finEmpRepo,
        private KpiExcel                 $excel,
        private KpiEmailer               $email,
    ) {
    }

    public function sendToTreasury(
        SecurityUser $currentUser,
        ?string $q = null,
        bool $onlyBoss = false,
        array $userIds = [],
    ): bool {
        $finEmpIds = $this->getEmployeeIdsForSend($currentUser, $onlyBoss, $userIds, $q);

        $data = $this->read->dataForExport($finEmpIds);

        if ($data === []) {
            throw new NotFoundHttpException("Не найдено данных для отправки при запрашиваемых параметрах");
        }

        $grouped = [];
        foreach ($this->distributeByFiles($data) as $key => $batch) {
            [$month, $type, $inRussia, $enterpriseId, $enterpriseName] = explode(':', $key);
            $inRussia = (bool) $inRussia;
            $enterpriseId = (int) $enterpriseId;
            $monthDate = new DateTimeImmutable("01.$month");
            $typeEnum = KpiType::from((int) $type);

            if (!isset($grouped[$enterpriseId])) {
                $grouped[$enterpriseId] = [];
            }

            $grouped[$enterpriseId][] = $this->excel
                ->clear()
                ->setName($monthDate, $enterpriseName, $typeEnum, $inRussia)
                ->setContent($batch)
                ->getFile()
            ;
        }

        $enterpriseIds = array_keys($grouped);
        $mapEnterpricesToEmails = $this->read->getResponsibleEmailsByEnterprises($enterpriseIds);
        $departmentName = $this->getDepartmentsAsString($currentUser->id);

        foreach ($grouped as $enterpriseId => $files) {
            if ($enterpriseId === 0) {
                $email = self::DEFAULT_EMAIL_WITHOUT_ENTERPRISE;
            } else {
                $email = $mapEnterpricesToEmails[$enterpriseId] ?? self::DEFAULT_EMAIL_WITHOUT_RESPONSE;
            }
            $this->email->send([$email], $files, $departmentName, $currentUser->name);
        }

        return $this->write->sendToTreasury($finEmpIds);
    }

    private function getEmployeeIdsForSend(SecurityUser $currentUser, bool $onlyBoss, array $userIds, ?string $q = null): array
    {
        $finEmpIds = $this->read->findEmpForExport($currentUser->id, $q, $onlyBoss);

        $isKpiSuperBoss = $this->secRepo->hasCpAction($currentUser->id, 'accured_kpi.accured_kpi_superboss');
        if ($isKpiSuperBoss) {
            $empIdsBosses = $this->read->bossListForExport($q);
            $finEmpIdsBosses = $this->cpEmpIdsToFinEmpIds($empIdsBosses);
            $finEmpIds = [...$finEmpIds, ...$finEmpIdsBosses];
        }

        return $this->prepareUserList($finEmpIds, $userIds);
    }

    // Получаем список департаментов, учитывая заместителей
    private function getDepartmentsAsString(int $userId): string
    {
        $departmentNames = [];

        $users = [$userId, ...$this->read->whoDeputied($userId)];
        foreach ($users as $userId) {
            $departmentNames[] = $this->secRepo->getDepartmentNameWhereBoss($userId);
        }
        return implode(', ', $departmentNames);
    }

    /**
     * распределяем записи о KPI по комбинации нескольких признаков (см. KPI-78, KPI-134)
     * - вид начислений
     * - расчетный период
     * - Россия или другие страны
     * - предприятие (может быть пустым)
     */
    private function distributeByFiles(array $data): array
    {
        $pool = [];
        foreach ($data as $item) {
            $month = (new DateTimeImmutable($item['dt']))->format('m.Y');
            $type = match (true) {
                $item['four_months_bonus'] !== null => KpiType::QUARTERLY->value,
                $item['two_month_bonus'] !== null   => KpiType::BIMONTHLY->value,
                default                             => KpiType::MONTHLY->value,
            };

            // Россия по умолчанию
            $inRussia = $item['enterprise_country'] === null || $item['enterprise_country'] === '1';

            if ($item['enterprise_name'] === null) {
                // с пустым enterprise_name - в файл "Страны"
                $inRussia = false;
                $item['enterprise_name'] = 'Сотрудники без предприятия';
            }
            $enterpriseId = $item['enterprise_id'];
            $enterpriseName = $item['enterprise_name'];

            $key = "$month:$type:$inRussia:$enterpriseId:$enterpriseName";
            $pool[$key][] = $item;
        }
        return $pool;
    }

    private function prepareUserList(array $finEmpIds, array $userIds): array
    {
        if ($finEmpIds === []) {
            return [];
        }

        if ($userIds !== []) {
            $finEmpIdsFiltered = $this->cpEmpIdsToFinEmpIds($userIds);
            // тех, кого нельзя отправить в казначейство - просто игнорируем #KPI-102
            $finEmpIds = array_values(array_intersect($finEmpIds, $finEmpIdsFiltered));
        }
        return $finEmpIds;
    }

    private function cpEmpIdsToFinEmpIds(array $userIds): array
    {
        return $this->finEmpRepo
            ->findBy([
                'cp_id' => array_unique($userIds),
            ])
            ->map(static fn (FinEmployee $finEmp): int => $finEmp->getFinEmpId())
            ->unique()
            ->toArray()
        ;
    }
}
