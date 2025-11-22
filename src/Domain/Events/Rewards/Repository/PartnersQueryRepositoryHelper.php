<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use Database\Connection\ParamType;
use DateTimeImmutable;

class PartnersQueryRepositoryHelper
{
    public static function prepareParameterTypes(): array
    {
        return [
            'program_id_list'       => ParamType::ARRAY_INTEGER,
            'nomination_id_list'    => ParamType::ARRAY_INTEGER,
            'reward_id_list'        => ParamType::ARRAY_INTEGER,
            'nomination_start_date' => ParamType::DATE,
            'nomination_end_date'   => ParamType::DATE,
            'reward_start_date'     => ParamType::DATE,
            'reward_end_date'       => ParamType::DATE,
            'contracts'             => ParamType::ARRAY_STRING,
            'partner_id_list'       => ParamType::ARRAY_INTEGER,
        ];
    }

    public static function resolveWinDateCondition(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): string
    {
        $rewardWinDateCondition = "";

        if ($startDate && is_null($endDate)) {
            $rewardWinDateCondition = "and rs.win_dt >= :nomination_start_date";
        } elseif ($endDate && is_null($startDate)) {
            $rewardWinDateCondition = "and rs.win_dt <= :nomination_end_date";
        } elseif ($startDate && $endDate) {
            $rewardWinDateCondition = "and (rs.win_dt between :nomination_start_date and :nomination_end_date)";
        }

        return $rewardWinDateCondition;
    }

    public static function resolveIssuedDateCondition(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): string
    {
        $rewardIssuedDateCondition = "";

        if ($startDate && is_null($endDate)) {
            $rewardIssuedDateCondition = "and rs.reward_date >= :reward_start_date";
        } elseif ($endDate && is_null($startDate)) {
            $rewardIssuedDateCondition = "and rs.reward_date <= :reward_end_date";
        } elseif ($startDate && $endDate) {
            $rewardIssuedDateCondition = "and (rs.reward_date between :reward_start_date and :reward_end_date)";
        }

        return $rewardIssuedDateCondition;
    }
}
