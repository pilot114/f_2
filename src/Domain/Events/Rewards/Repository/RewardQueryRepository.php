<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Reward;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Reward>
 */
class RewardQueryRepository extends QueryRepository
{
    protected string $entityName = Reward::class;

    /**
     * @param array<int> $ids
     * @return Enumerable<int, Reward>
     */
    public function getByIds(array $ids): Enumerable
    {
        $sql = $this->getBaseQuery() . " where gds.id IN (:ids)";

        $rewards = $this->query($sql, [
            'ids' => $ids,
        ], [
            'ids' => ParamType::ARRAY_INTEGER,
        ]);

        $foundRewardsIds = $rewards->map(fn (Reward $reward): int => $reward->id)->all();
        $missingRewardIds = array_diff($ids, $foundRewardsIds);

        if ($missingRewardIds !== []) {
            $missingIdsString = implode(', ', $missingRewardIds);
            throw new EntityNotFoundDatabaseException("Не найдены подарки с id = ({$missingIdsString})");
        }

        return $rewards;
    }

    public function getOne(int $id): Reward
    {
        $sql = $this->getBaseQuery() . " where gds.id = :id";
        $rewards = $this->query($sql, [
            'id' => $id,
        ]);
        $reward = $rewards->first();

        if (!$reward) {
            throw new EntityNotFoundDatabaseException("Не найдена награда с id = {$id}");
        }

        return $reward;
    }

    /** @return Enumerable<int, Reward> */
    public function getRewardsForVerificationFilter(array $nominationIds, int $countryId): Enumerable
    {
        $sql = "select
                ppg.id,
                t.name name,
                ppg.gds_id product_id,
                pn.id nomination_id,
                pn.name nomination_name,
                pp.id nomination_program_id,
                pp.name nomination_program_name
                from tehno.tovar t
                join net.pd_present_gds ppg on ppg.gds_id = t.id
                join net.pd_nomination_presents pnp on pnp.id = ppg.nomination_pr
                join net.pd_nominations pn on pn.id = pnp.nomination
                join net.pd_prog pp on pp.id = pn.prog
                join net.pd_present_gds_country_status ppgcs
                    on ppgcs.pd_present_gds_id = ppg.id
                    and ppgcs.country_id = :country_id
                    and ppgcs.status = 1
                 
                where pnp.nomination in (:nomination_ids)";

        return $this->query(
            $sql,
            [
                'nomination_ids' => $nominationIds,
                'country_id'     => $countryId,
            ],
            [
                'nomination_ids' => ParamType::ARRAY_INTEGER,
            ]
        );
    }

    private function getBaseQuery(): string
    {
        return <<<SQL
            select 
                gds.id, 
                t.name,
                t.id product_id,
                gds.commentary,
                ------------------------
                pn.id nomination_id,
                pn.name nomination_name,
                ------------------------
                pp.id nomination_program_id,
                pp.name nomination_program_name,
                ------------------------
                stat.id statuses_id,
                stat.status statuses_status,
                stat.country_id statuses_country_id,
                c.name statuses_country_name,
                ------------------------------
                grt.reward_type_id type_id, -- типы наград
                rt.name type_name 
            from net.pd_present_gds gds
            join tehno.tovar t on t.id = gds.gds_id
            join net.pd_nomination_presents pnp on pnp.id = gds.nomination_pr
            join net.pd_nominations pn on pn.id = pnp.nomination
            join net.pd_prog pp on pp.id = pn.prog
            left join net.pd_present_gds_country_status stat on stat.pd_present_gds_id = gds.id
            left join tehno.country c on c.country = stat.country_id
            left join net.pd_gds_reward_types grt on grt.gds_id = t.id
            left join net.pd_reward_types rt on rt.id = grt.reward_type_id 
        SQL;
    }

    /** @return Enumerable<int, Reward> */
    public function getRewardsInNomination(int $rewardId, int $countryId): Enumerable
    {
        $sql = "select
                ppg.id id,
                t.name name,
                ppg.gds_id product_id,
                -----------------------
                pn.id nomination_id,
                pn.name nomination_name,
                ------------------------
                pp.id nomination_program_id,
                pp.name nomination_program_name,
                ------------------------
                ppgcs.id statuses_id,
                ppgcs.status statuses_status,
                ppgcs.country_id statuses_country_id, 
                c.name statuses_country_name
                from tehno.tovar t
                join net.pd_present_gds ppg on ppg.gds_id = t.id
                join net.pd_nomination_presents pnp on pnp.id = ppg.nomination_pr
                join net.pd_nominations pn on pn.id = pnp.nomination
                join net.pd_prog pp on pp.id = pn.prog
                left join net.pd_present_gds_country_status ppgcs on ppgcs.pd_present_gds_id = ppg.id
                left join tehno.country c on c.country = ppgcs.country_id
                where 1=1
                and ppgcs.country_id = :country_id
                and pnp.id = (select p.nomination_pr from  net.pd_present_gds p where p.id = :present_id)
                ";

        return $this->query(
            $sql,
            [
                'present_id' => $rewardId,
                'country_id' => $countryId,
            ]
        );
    }
}
