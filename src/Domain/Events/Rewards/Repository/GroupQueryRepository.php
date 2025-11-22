<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\RewardTypeRequest;
use App\Domain\Events\Rewards\Entity\Group;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Group>
 */
class GroupQueryRepository extends QueryRepository
{
    protected string $entityName = Group::class;

    /**
     * @TODO пока не решили что делать с денежными наградами.
     * Скрываем их здесь - в базе нет признака как их вычислить
     */
    private const EXCLUDED_REWARD_IDS = [
        118175909,
        9548626,
        9548628,
        9548713,
        9548716,
        14762955,
        121016568,
        138490008,
        118175783,
        118175901,
        14734453,
        118175842,
        121016535,
        14734464,
        118175817,
        138490061,
        9548625,
        9548627,
        9548715,
        9548718,
        118175788,
        118175874,
        118175886,
        118175761,
        118175765,
        121016573,
        138490039,
        138490079,
        121016585,
        117430229,
        118175850,
        123293102,
        123293106,
        118175825,
    ];

    /**
     * @param RewardTypeRequest[] $rewardTypes
     * @return Enumerable<int, Group>
     */
    public function getGroups(int|FilterOption $country, ?string $search, bool $status, array $rewardTypes): Enumerable
    {
        $countryCondition = $this->resolveCountryCondition($country);
        $searchCondition = $this->resolveSearchCondition($search);
        $statusCondition = $this->resolveStatusCondition($status);
        $customExcludedRewardsCondition = $this->resolveCustomExcludedRewardsCondition();
        $rewardTypesCondition = $this->resolveRewardTypeConditions($rewardTypes);

        $sql = "select
                    prog_gr.pd_group_id id,  -- группы программ
                    prog_pg.name name,
                    ------------------------------------------
                    prog.id programs_id, -- программы
                    prog.name programs_name,
                    ------------------------------------------
                    nom.id programs_nominations_id, -- номинации
                    nom.name programs_nominations_name, 
                    ------------------------------------------
                    grt.reward_type_id programs_nominations_rewards_type_id, -- типы наград
 					rt.name programs_nominations_rewards_type_name, 
 					-----------------------------------------------------------
                    gds.id programs_nominations_rewards_id, -- награды
                    t.name programs_nominations_rewards_name,
                    gds.gds_id programs_nominations_rewards_product_id,
                    nom.id programs_nominations_rewards_nomination_id, -- номинации
                    nom.name programs_nominations_rewards_nomination_name,
                    prog.id programs_nominations_rewards_nomination_program_id, -- программы
                    prog.name programs_nominations_rewards_nomination_program_name,
                    gds.commentary programs_nominations_rewards_commentary,
                    ------------------------------------------
                    stat.id programs_nominations_rewards_statuses_id,
                    stat.status programs_nominations_rewards_statuses_status, -- статус мастер-кода
                    stat.country_id programs_nominations_rewards_statuses_country_id, -- страна мастер-кода
                    c.name programs_nominations_rewards_statuses_country_name
                    from
                      net.pd_prog prog
                      join net.pd_nominations nom on nom.prog = prog.id
                      join net.pd_nomination_presents pres on pres.nomination = nom.id
                      join net.pd_present_gds gds on gds.nomination_pr = pres.id
                      join tehno.tovar t on t.id = gds.gds_id
                      left join net.pd_present_gds_country_status stat on stat.pd_present_gds_id = gds.id
                      left join tehno.country c on c.country =  stat.country_id
                      left join net.pd_prog_group prog_gr on prog_gr.pd_prog_id = prog.id and prog_gr.pd_present_gds_id is null
                      left join net.pd_group prog_pg on prog_pg.id = prog_gr.pd_group_id and prog_pg.group_type = 1 
                      left join net.pd_gds_reward_types grt on grt.gds_id = t.id
					  left join net.pd_reward_types rt on rt.id = grt.reward_type_id 
                    where
                      1=1
                      and prog.id <> 6438347 --Исключить программу \"Звездный наставник\"  
                      $countryCondition
                      $searchCondition 
                      $statusCondition
                      $customExcludedRewardsCondition
                      $rewardTypesCondition
                      ";

        $this->conn->enableLazyLoading(0, 10000);
        return $this->query(
            $sql,
            [
                'country_id' => $country,
                'search'     => $search,
                'typeIds'    => array_column(array_filter($rewardTypes, fn ($type): bool => is_int($type->id)), 'id'),
            ],
            [
                'typeIds' => ParamType::ARRAY_INTEGER,
            ]
        );
    }

    /**
     * @return Enumerable<int, Group>
     */
    public function getAvailableGroups(): Enumerable
    {
        $sql = "select
                    gr.id id,
                    gr.name name
                    from
                      net.pd_group gr
                    where
                      1=1
                      and gr.group_type = 1  
                      ";

        return $this->query($sql);
    }

    private function resolveCountryCondition(int|FilterOption $country): string
    {
        if (is_int($country)) {
            return "AND stat.country_id = :country_id";
        }

        return match ($country) {
            FilterOption::Q_ANY  => "",
            FilterOption::Q_SOME => "AND stat.country_id IS NOT NULL",
            FilterOption::Q_NONE => "AND stat.country_id IS NULL",
        };
    }

    private function resolveSearchCondition(?string $search): string
    {
        if ($search === null) {
            return "";
        }

        return "and (
            lower(prog.name) like '%' || lower(:search) || '%'
            or lower(t.name) like '%' || lower(:search) || '%'
        )";
    }

    private function resolveStatusCondition(bool $status = false): string
    {
        if ($status) {
            return "and stat.status = 1";
        }
        return "";
    }

    private function resolveCustomExcludedRewardsCondition(): string
    {
        return "and gds.id not in (" . implode(',', self::EXCLUDED_REWARD_IDS) . ")";
    }

    /**
     * @param RewardTypeRequest[] $rewardTypes
     */
    private function resolveRewardTypeConditions(array $rewardTypes): string
    {
        $ids = [];
        $rewardsWithOutType = false;
        foreach ($rewardTypes as $type) {
            if ($type->id instanceof FilterOption) {
                $rewardsWithOutType = true;
            } else {
                $ids[] = $type->id;
            }
        }
        $rewardsWithOutTypeCondition = $rewardsWithOutType ? "or grt.reward_type_id is null" : "";

        return $ids === []
            ? $rewardsWithOutType ? "and grt.reward_type_id is null" : ""
            : "and (grt.reward_type_id in (:typeIds) $rewardsWithOutTypeCondition)";
    }
}
