<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\UseCase;

use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use App\Domain\Partners\SaleStructure\Exception\PartnerDomainException;
use App\Domain\Partners\SaleStructure\Repository\PartnerInfoRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use DateTimeImmutable;

class PartnerInfoUseCase
{
    public function __construct(
        private PartnerInfoRepository $partnerInfoRepository,
        private SecurityUser $user,
    ) {
    }

    protected function accessAllowed(?int $countryCode): bool
    {
        if (!$countryCode) {
            return false;
        }
        $userId = $this->user->id;
        if ($userId === 4155 || $userId === 1331) {
            return true;
        }
        return ($countryCode === 4 && $userId === 1303)
            || ($countryCode === 211 && $userId === 3945)
            || ($countryCode === 301 && $userId === 6808);
    }

    public function getByContract(string $contract): PartnerInfo
    {
        $notFoundException = new PartnerDomainException(
            'Контракт не найден',
            404,
            [
                "details" => "Проверьте правильность номера и повторите попытку.",
            ]);
        $employee = $this->partnerInfoRepository->getEmployeeByContract($contract);
        if (!$employee instanceof PartnerInfo) {
            throw $notFoundException;
        }
        if (!$this->accessAllowed($employee->getCountryCode())) {
            throw new PartnerDomainException(
                'Нет прав на просмотр',
                403,
                [
                    "details" => "Контракт найден, но относится к другой стране.",
                ]);
        }
        if ($employee->getDateEnd() instanceof DateTimeImmutable) {
            throw new PartnerDomainException(
                'Контракт закрыт',
                404,
                [
                    "details" => "Просмотр данных невозможен",
                ]);
        }
        $queryData = $this->partnerInfoRepository->getEmployeeInfo($employee->getId());
        if (empty($queryData['o_result'][0])) {
            throw $notFoundException;
        }
        $data = $queryData['o_result'][0];
        $rankName = $this->partnerInfoRepository->getRankNameById((int) $data['rang']);
        return new PartnerInfo(
            (int) $data['id'],
            $data['name'],
            $data['contract'],
            ucfirst(mb_convert_case($data['country_name'], MB_CASE_LOWER, "UTF-8")),
            $employee->getCountryCode(),
            (int) $data['rang'],
            $employee->getDateEnd(),
            $data['win_dt_career'] ? new DateTimeImmutable($data['win_dt_career']) : null,
            $rankName
        );
    }

}
