<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\Deputy;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Deputy>
 */
class DeputyCommandRepository extends CommandRepository
{
    protected string $entityName = Deputy::class;

    public function createDeputy(Deputy $deputy): Deputy
    {
        $raw = $this->normalize($deputy);
        $result = $this->conn->procedure('tehno.pkpi.kpi_deputy_add', [
            'pUser_id'        => $raw['user_id'],
            'pDeputy_user_id' => $raw['deputy_user_id'],
            'pStart_date'     => $raw['start_date'],
            'pEnd_date'       => $raw['end_date'],
            'oDeputi_id'      => null,
        ], [
            'pDeputy_user_id' => [ParamMode::IN, ParamType::INTEGER],
            'pStart_date'     => [ParamMode::IN, ParamType::DATE],
            'pEnd_date'       => [ParamMode::IN, ParamType::DATE],
            'oDeputi_id'      => [ParamMode::OUT, ParamType::INTEGER],
        ]);
        $deputy->id = $result['oDeputi_id'];
        return $deputy;
    }

    public function updateDeputy(Deputy $deputy): Deputy
    {
        $raw = $this->normalize($deputy);
        $this->conn->procedure('tehno.pkpi.kpi_deputy_edit', [
            'pDeputi_id'      => $raw['id'],
            'pDeputy_user_id' => $raw['deputy_user_id'],
            'pStart_date'     => $raw['start_date'],
            'pEnd_date'       => $raw['end_date'],
        ], [
            'pDeputy_user_id' => [ParamMode::IN, ParamType::INTEGER],
            'pStart_date'     => [ParamMode::IN, ParamType::DATE],
            'pEnd_date'       => [ParamMode::IN, ParamType::DATE],
        ]);
        return $deputy;
    }

    public function deleteDeputy(int $deputyId): bool
    {
        $this->conn->procedure('tehno.pkpi.kpi_deputy_delete', [
            'pDeputi_id' => $deputyId,
        ], [
            'pDeputi_id' => [ParamMode::IN, ParamType::INTEGER],
        ]);
        return true;
    }
}
