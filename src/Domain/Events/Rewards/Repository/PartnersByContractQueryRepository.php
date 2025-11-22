<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\PartnersByContractsRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Partner>
 */
class PartnersByContractQueryRepository extends QueryRepository
{
    protected string $entityName = Partner::class;

    /** @return Enumerable<int, Partner> */
    public function getWithActiveReward(PartnersByContractsRequest $request): Enumerable
    {
        [
            $programsCondition,
            $nominationsCondition,
            $rewardStatusCondition,
            $rewardsCondition,
            $rewardWinDateCondition,
            $rewardIssuedDateCondition,
            $countryCondition,
            $partnerName,
            $contractsCondition,
        ] = $this->buildCommonConditions($request);

        $penaltiesFields = $this->makePenaltiesSelectFields($request->withPenalties);
        $penaltiesSubQuery = $this->makePenaltiesSubQuery($request->withPenalties);

        $sql = "
                select distinct
                e.id,
                e.name name,
                e.contract contract,
                case when e.familya = 'Y' then 1 else 0 end is_family,
                $penaltiesFields
                --------------------------
                c.tehno_id country_id,
                cntr.name  country_name,
                ---------------------------------------
                reward.id rewardissuancestate_id,            
                reward.result_id rewardissuancestate_calculation_result_id,            
                reward.prog_id rewardissuancestate_program_id,
                reward.prog_name rewardissuancestate_program_name,
                reward.nomination_id rewardissuancestate_nomination_id,
                reward.nomination_name rewardissuancestate_nomination_name,
                reward.is_reward rewardissuancestate_status,
                reward.win_dt rewardissuancestate_win_date,
                reward.reward_date rewardissuancestate_rewarddate,
                reward.cnt rewardissuancestate_count,
                reward.note rewardissuancestate_note,
                0 rewardissuancestate_deleted,
                reward.user_id rewardissuancestate_user_id,
                reward.user_name rewardissuancestate_user_name,
                reward.present_id rewardissuancestate_reward_id,
                reward.present_name rewardissuancestate_reward_name,
                reward.product_id rewardissuancestate_reward_product_id,
                reward.status_id rewardissuancestate_reward_statuses_id,
                reward.status rewardissuancestate_reward_statuses_status,
                reward.status_country_id rewardissuancestate_reward_statuses_country_id,
                reward.status_country_name rewardissuancestate_reward_statuses_country_name,
                -----------------------------------------------                                
                emp_st.id status_id,
                emp_st.employee_id partner_id,
                emp_st.pd_status_id status_pd_status_id,
                emp_st.reward_count status_reward_count,
                net.calc_pd_employee_status (e.id, 1) actual_status,
                net.calc_pd_employee_status (e.id, 3) actual_reward_count
                from net.employee e
                join net.employee cok on cok.id = e.cok
                join net.country c on c.id = cok.country
                join tehno.country cntr on cntr.country = c.tehno_id
                join (--список наград
                        select rs.id,
                               rs.present_id result_id,
                               rs.employee,
                               rs.win_dt,
                               pnp.family,
                               rs.prog prog_id,
                               pp.name prog_name,
                               rs.nomination nomination_id,
                               pn.name nomination_name,
                               ppg.id present_id,
                               ppg.gds_id product_id,
                               t.name present_name,
                               ppgcs.status status_id,
                               ppgcs.status,
                               ppgcs.country_id status_country_id,
                               c.name status_country_name,
                               rs.note,
                               rs.cnt,
                               rs.is_reward,
                               rs.reward_date,
                               rs.reward_user user_id,
                               e.name user_name
                        from net.pd_reward_status rs
                        join net.pd_prog pp on pp.id = rs.prog and pp.id != 6438347
                        join net.pd_prog_group prog_grp on prog_grp.pd_prog_id = pp.id and prog_grp.pd_present_gds_id is null and prog_grp.pd_group_id != 3
                        join net.pd_nominations pn on pn.id = rs.nomination
                        join net.pd_nomination_presents pnp on pnp.nomination = pn.id
                        join net.pd_present_gds ppg on ppg.nomination_pr = pnp.id and ppg.gds_id = rs.gds_id
                        join net.pd_present_gds_country_status ppgcs on ppgcs.pd_present_gds_id = ppg.id and ppgcs.status = 1
                        join tehno.country c on c.country =  ppgcs.country_id
                        join tehno.tovar t on t.id = rs.gds_id
                        left join test.cp_emp e on e.id = rs.reward_user
                        where 1=1
                        $programsCondition
                        $nominationsCondition
                        $rewardStatusCondition
                        $rewardsCondition
                        $rewardWinDateCondition
                        $rewardIssuedDateCondition
                        ) reward on reward.employee = e.id and reward.status_country_id = c.tehno_id and reward.family=(case when e.familya='Y' then 1 else 0 end)
                $penaltiesSubQuery
                left join net.pd_employee_status emp_st on emp_st.employee_id = e.id
                 
                where e.d_end is null
                $countryCondition
                $contractsCondition
        ";

        return $this->query(
            $sql,
            [
                'contracts'             => $request->contracts,
                'partnerName'           => $partnerName,
                'program_id_list'       => $request->programIds,
                'nomination_id_list'    => $request->nominationIds,
                'reward_id_list'        => $request->rewardIds,
                'reward_status_id'      => $request->rewardIssuanceState?->value,
                'nomination_start_date' => $request->nominationStartDate,
                'nomination_end_date'   => $request->nominationEndDate,
                'reward_start_date'     => $request->rewardStartDate,
                'reward_end_date'       => $request->rewardEndDate,
                'country_id'            => $request->country instanceof FilterOption ? null : $request->country,
            ],
            PartnersQueryRepositoryHelper::prepareParameterTypes()
        );
    }

    /** @return Enumerable<int, Partner> */
    public function getWithDeletedRewards(PartnersByContractsRequest $request): Enumerable
    {
        [
            $contractsCondition,
            $countryCondition,
            $rewardStatusCondition,
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardWinDateCondition,
            $partnerName,
            $rewardIssuedDateCondition,
        ] = $this->buildCommonConditions($request);

        $sql = "
        SELECT DISTINCT 
               e.id
               , e.name name
               , e.contract contract
               , CASE 
                   WHEN e.familya = 'Y' 
                     THEN 1 
                       ELSE 0 
                         END is_family
        ----------------------------------------------------------------
               , c.tehno_id country_id
               , cntr.name  country_name
        ----------------------------------------------------------------
               , rs.id rewardissuancestate_id
               , rs.present_id rewardissuancestate_calculation_result_id
               , p.id rewardissuancestate_program_id
               , p.name rewardissuancestate_program_name
               , rs.nomination rewardissuancestate_nomination_id
               , n.name rewardissuancestate_nomination_name
               , rs.is_reward rewardissuancestate_status
               , rs.win_dt rewardissuancestate_win_date
               , rs.reward_date rewardissuancestate_rewarddate
               , rs.cnt rewardissuancestate_count
               , rs.note rewardissuancestate_note
               , rs.reward_user rewardissuancestate_user_id
               , emp.name rewardissuancestate_user_name
               , NULL rewardissuancestate_reward_id
               , t.name rewardissuancestate_reward_name
               , NULL rewardissuancestate_reward_statuses_id
               , NULL rewardissuancestate_reward_statuses_status
               , NULL rewardissuancestate_reward_statuses_country_id
               , NULL rewardissuancestate_reward_statuses_country_name
               , 1 rewardissuancestate_deleted
        
        FROM net.pd_reward_status rs
        LEFT JOIN net.pd_prog p ON p.id = rs.prog AND p.id != 6438347
        LEFT JOIN net.pd_nominations n ON n.id = rs.nomination
        JOIN tehno.tovar t ON t.id = rs.gds_id
        JOIN net.employee e ON e.id = rs.employee AND e.d_end IS NULL
        join net.employee cok on cok.id = e.cok
        join net.country c on c.id = cok.country
        join tehno.country cntr on cntr.country = c.tehno_id
        LEFT JOIN test.cp_emp emp ON emp.id = rs.reward_user
        
        WHERE NOT EXISTS (SELECT NULL FROM net.pd_present_gds pg WHERE pg.gds_id = rs.gds_id)
        AND t.id IN (SELECT gds_id FROM net.pd_gds_reward_types WHERE reward_type_id = 1)
        $contractsCondition
        $countryCondition
        $rewardStatusCondition
        $programsCondition
        $nominationsCondition
        $rewardsCondition
        $rewardWinDateCondition
        $rewardIssuedDateCondition
        
        ORDER BY p.name, n.name, t.name
        ";

        return $this->query(
            $sql,
            $this->buildParameter($partnerName, $request),
            PartnersQueryRepositoryHelper::prepareParameterTypes()
        );
    }

    private function buildParameter(?string $partnerName, PartnersByContractsRequest $request): array
    {
        return [
            'contracts'             => $request->contracts,
            'partnerName'           => $partnerName,
            'program_id_list'       => $request->programIds,
            'nomination_id_list'    => $request->nominationIds,
            'reward_id_list'        => $request->rewardIds,
            'reward_status_id'      => $request->rewardIssuanceState?->value,
            'nomination_start_date' => $request->nominationStartDate,
            'nomination_end_date'   => $request->nominationEndDate,
            'reward_start_date'     => $request->rewardStartDate,
            'reward_end_date'       => $request->rewardEndDate,
            'country_id'            => $request->country instanceof FilterOption ? null : $request->country,
        ];
    }

    private function buildCommonConditions(PartnersByContractsRequest $request): array
    {
        $programsCondition = $request->programIds === [] ? "" : "and rs.prog in (:program_id_list)";
        $nominationsCondition = $request->nominationIds === [] ? "" : "and rs.nomination in (:nomination_id_list)";
        $rewardStatusCondition = $request->rewardIssuanceState instanceof RewardIssuanceStateStatusType ? "and rs.is_reward = :reward_status_id" : "";
        $rewardsCondition = $request->rewardIds === [] ? "" : "and rs.gds_id in (:reward_id_list)";
        $rewardWinDateCondition = PartnersQueryRepositoryHelper::resolveWinDateCondition($request->nominationStartDate, $request->nominationEndDate);
        $rewardIssuedDateCondition = PartnersQueryRepositoryHelper::resolveIssuedDateCondition($request->rewardStartDate, $request->rewardEndDate);
        $countryCondition = $request->country instanceof FilterOption ? "" : "and cntr.country = :country_id";
        $partnerName = mb_strtoupper(implode(' ', $request->contracts));
        $partnerName = ctype_space($partnerName) ? null : $partnerName;
        $contractsCondition = $partnerName ? "and (e.contract in (:contracts) OR upper(e.name) LIKE '%'||:partnerName||'%')" : "and e.contract in (:contracts)";

        return [
            $programsCondition,
            $nominationsCondition,
            $rewardStatusCondition,
            $rewardsCondition,
            $rewardWinDateCondition,
            $rewardIssuedDateCondition,
            $countryCondition,
            $partnerName,
            $contractsCondition,
        ];
    }

    private function makePenaltiesSelectFields(bool $withBlackList): string
    {
        $blackListFields = '  
            black.id penalties_id,
            black.name penalties_name,
            black.data_open penalties_start,
            black.data_close penalties_end,
            black.prim penalties_prim,';

        return $withBlackList ? $blackListFields : '';
    }

    private function makePenaltiesSubQuery(bool $withBlackList): string
    {
        $blackListSubQuery = "
        left join (--черный список
            select b.blacklist || 'black' id, emp.id emp_id, 'Статья нарушения: ' || bt.name name, b.data_open, b.data_close, b.prim
            from tehno.blacklist b
            join tehno.blacklist_type bt on bt.id = b.shtraf
            join net.employee emp on emp.contract = b.contract
            where b.shtraf in (31, 32)
            and b.data_open <= sysdate
            and (b.data_close >= sysdate or b.data_close is null)
            union all
            select pe.id || 'excluded' id, pe.emp_id, 'Исключен из программы: ' || pp.name name, pe.start_dt data_open,
            to_date('01.01.3000') data_close,
            pe.note prim
            from net.prz_except pe
            join net.pd_prog pp on pp.id = pe.prog_id
            where pe.included = 0
            and pe.prog_id != 6438347
            and pe.start_dt <= sysdate) black on black.emp_id = e.id";

        return $withBlackList ? $blackListSubQuery : '';
    }
}
