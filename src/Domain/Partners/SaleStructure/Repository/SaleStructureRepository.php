<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\Repository;

use App\Domain\Partners\SaleStructure\Entity\PartnerSaleStructure;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use DateTimeImmutable;

/**
 * @extends QueryRepository<PartnerSaleStructure>
 */
class SaleStructureRepository extends QueryRepository
{
    protected string $entityName = PartnerSaleStructure::class;

    public function getCountryCode(string $countryName): ?int
    {
        $countryName = mb_strtolower($countryName);
        $data = $this->conn->query("SELECT country FROM tehno.country WHERE lower(name) = '$countryName'");
        if (!empty($data->current())) {
            return (int) $data->current()['country'];
        }
        return null;
    }

    public function getCurrencyNameByCountryCode(?int $countryCode): ?string
    {
        $data = $this->conn->query("SELECT c.name name
            FROM tehno.currency c
            JOIN tehno.country cntr ON cntr.curr = c.currency
            WHERE cntr.country = $countryCode");
        if (!empty($data->current())) {
            return $data->current()['name'] ?? null;
        }
        return null;

    }

    public function getSaleStructure(string $contract, DateTimeImmutable $from, DateTimeImmutable $till): array
    {
        return $this->conn->procedure(
            'net.point_sale_base.get_oo_country',
            [
                'pContract' => $contract,
                'pbeg'      => $from,
                'pend'      => $till,
                'pOut'      => null,
            ],
            [
                'pContract' => [ParamMode::IN, ParamType::STRING],
                'pbeg'      => [ParamMode::IN, ParamType::DATE],
                'pend'      => [ParamMode::IN, ParamType::DATE],
                'pOut'      => [ParamMode::OUT, ParamType::CURSOR],
            ]
        )['pOut'];
    }

}
