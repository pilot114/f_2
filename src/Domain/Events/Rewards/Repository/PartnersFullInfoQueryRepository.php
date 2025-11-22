<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Partner>
 */
class PartnersFullInfoQueryRepository extends QueryRepository
{
    protected string $entityName = Partner::class;

    public function getWithActiveRewards(PartnerFullInfoRequest $request): Partner
    {
        [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardStatusCondition,
            $rewardWinDateCondition,
            $rewardIssuedDateCondition,
        ] = $this->buildCommonCondition($request);

        $sql = "select distinct
                e.id,
                e.name,
                e.contract contract,
                case when e.familya = 'Y' then 1 else 0 end is_family,
                ----------------------------------
                c.tehno_id country_id,
                cntr.name  country_name,
                -----------------------------------
                rang.emp_id rang_id,
                rang.max_rang rang_current_rang,
                r.name rang_current_rang_name,
                rang.max_dt rang_current_rang_date,
                --------------------------------------
                black.id penalties_id,
                black.name penalties_name,
                black.data_open penalties_start,
                black.data_close penalties_end,
                black.prim penalties_prim,
                ---------------------------------------
                emp_st.id status_id,
                emp_st.employee_id status_partner_id,
                emp_st.pd_status_id status_pd_status_id,
                emp_st.reward_count status_reward_count,
                emp_st.penalty_count status_penalty_count,
                net.calc_pd_employee_status (e.id, 1) actual_status,
                net.calc_pd_employee_status (e.id, 3) actual_reward_count,
                net.calc_pd_employee_status (e.id, 2) actual_penalty_count,
                -------------------------------------------
                reward.reward_status_id rewardissuancestate_id,            
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
                reward.user_id rewardissuancestate_user_id,
                reward.user_name rewardissuancestate_user_name,
                reward.present_id rewardissuancestate_reward_id,
                reward.present_name rewardissuancestate_reward_name,
                reward.product_id rewardissuancestate_reward_product_id,
                reward.status_id rewardissuancestate_reward_statuses_id,
                reward.status rewardissuancestate_reward_statuses_status,
                reward.status_country_id rewardissuancestate_reward_statuses_country_id,
                reward.status_country_name rewardissuancestate_reward_statuses_country_name,
                0 rewardissuancestate_deleted

                from net.employee e
                join net.employee cok on cok.id = e.cok
                join net.country c on c.id = cok.country
                join tehno.country cntr on cntr.country = c.tehno_id
                join (select cm.emp_id,
                             max(cm.dt) keep (dense_rank first order by cm.dt desc) max_dt,
                             max(cm.rang) keep (dense_rank first order by cm.dt desc) max_rang
                      from net.cache_main cm
                      group by cm.emp_id) rang on rang.emp_id = e.id
                join net.rang r on r.rang = rang.max_rang
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
                            and pe.start_dt <= sysdate) black on black.emp_id = e.id
                left join (--список наград
                            select rs.id reward_status_id,
                                   rs.present_id result_id,
                                   rs.employee,
                                   pnp.family,
                                   rs.win_dt,
                                   rs.reward_date,
                                   rs.prog prog_id,
                                   pp.name prog_name,
                                   rs.nomination nomination_id,
                                   pn.name nomination_name,
                                 --  rs.gds_id present_id,
                                   ppg.id present_id,
                                   ppg.gds_id product_id,
                                   t.name present_name,
                                   ppgcs.country_id status_country_id,
                                   c.name status_country_name,
                                   ppgcs.id status_id,
                                   ppgcs.status,
                                   rs.note,
                                   rs.cnt,
                                   rs.is_reward,
                                   rs.reward_user user_id,
                                   e.name user_name
                            from net.pd_reward_status rs
                            join net.pd_prog pp on pp.id = rs.prog and pp.id != 6438347
                            join net.pd_prog_group prog_grp on prog_grp.pd_prog_id = pp.id and prog_grp.pd_present_gds_id is null and prog_grp.pd_group_id != 3
                            join net.pd_nominations pn on pn.id = rs.nomination
                            join net.pd_nomination_presents pnp on pnp.nomination = pn.id
                            join net.pd_present_gds ppg on ppg.nomination_pr = pnp.id and ppg.gds_id = rs.gds_id
                            join net.pd_present_gds_country_status ppgcs on ppgcs.pd_present_gds_id = ppg.id and ppgcs.status = 1
                            left join tehno.country c on c.country =  ppgcs.country_id
                            left join test.cp_emp e on e.id = rs.reward_user
                            join tehno.tovar t on t.id = rs.gds_id
                            where 1=1
                            $programsCondition
                            $nominationsCondition       
                            $rewardStatusCondition
                            $rewardsCondition
                            $rewardWinDateCondition
                            $rewardIssuedDateCondition
                            ) reward on reward.employee = e.id and reward.family=(case when e.familya='Y' then 1 else 0 end)                     
                            and reward.status_country_id = cntr.country
                left join net.pd_employee_status emp_st on emp_st.employee_id = e.id
                where 1=1
                and e.id = :partner_id
                ";

        $partner = $this->query($sql, [
            'partner_id'            => $request->partnerId,
            'reward_status_id'      => $request->rewardIssuanceState?->value,
            'program_id_list'       => $request->programIds,
            'nomination_id_list'    => $request->nominationIds,
            'reward_id_list'        => $request->rewardIds,
            'nomination_start_date' => $request->nominationStartDate,
            'nomination_end_date'   => $request->nominationEndDate,
            'reward_start_date'     => $request->rewardStartDate,
            'reward_end_date'       => $request->rewardEndDate,
        ],
            PartnersQueryRepositoryHelper::prepareParameterTypes()
        )->first();

        if (is_null($partner)) {
            throw new EntityNotFoundDatabaseException("не найден партнёр с id " . $request->partnerId);
        }

        return $partner;
    }

    public function getWithDeletedRewards(PartnerFullInfoRequest $request): Partner
    {
        [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardStatusCondition,
            $rewardWinDateCondition,
            $rewardIssuedDateCondition,
        ] = $this->buildCommonCondition($request);

        $sql = "
                select distinct
                    e.id,
                    e.name,
                    e.contract contract,
                    case when e.familya = 'Y' then 1 else 0 end is_family,
                    ----------------------------------
                    c.tehno_id country_id,
                    cntr.name  country_name,
                    -----------------------------------
                    rang.emp_id rang_id,
                    rang.max_rang rang_current_rang,
                    r.name rang_current_rang_name,
                    rang.max_dt rang_current_rang_date,
                    --------------------------------------
                    black.id penalties_id,
                    black.name penalties_name,
                    black.data_open penalties_start,
                    black.data_close penalties_end,
                    black.prim penalties_prim,
                    ---------------------------------------
                    emp_st.id status_id,
                    emp_st.employee_id status_partner_id,
                    emp_st.pd_status_id status_pd_status_id,
                    emp_st.reward_count status_reward_count,
                    emp_st.penalty_count status_penalty_count,
                    net.calc_pd_employee_status (e.id, 1) actual_status,
                    net.calc_pd_employee_status (e.id, 3) actual_reward_count,
                    net.calc_pd_employee_status (e.id, 2) actual_penalty_count,
                    -------------------------------------------
                    rs.id rewardissuancestate_id,
                    rs.present_id rewardissuancestate_calculation_result_id,
                    p.id rewardissuancestate_program_id,
                    p.name rewardissuancestate_program_name,
                    rs.nomination rewardissuancestate_nomination_id,
                    n.name rewardissuancestate_nomination_name,
                    rs.is_reward rewardissuancestate_status,
                    rs.win_dt rewardissuancestate_win_date,
                    rs.reward_date rewardissuancestate_rewarddate,
                    rs.cnt rewardissuancestate_count,
                    rs.note rewardissuancestate_note,
                    rs.reward_user rewardissuancestate_user_id,
                    emp.name rewardissuancestate_user_name,
                    NULL rewardissuancestate_reward_id,
                    t.name rewardissuancestate_reward_name,
                    NULL rewardissuancestate_reward_statuses_id,
                    NULL rewardissuancestate_reward_statuses_status,
                    NULL rewardissuancestate_reward_statuses_country_id,
                    NULL rewardissuancestate_reward_statuses_country_name,
                    1 rewardissuancestate_deleted
                            
            from net.employee e
            join net.employee cok on cok.id = e.cok
            join net.country c on c.id = cok.country
            join tehno.country cntr on cntr.country = c.tehno_id
            join (	select 
                    cm.emp_id,
                    max(cm.dt) keep (dense_rank first order by cm.dt desc) max_dt,
                    max(cm.rang) keep (dense_rank first order by cm.dt desc) max_rang
                    from net.cache_main cm
                    group by cm.emp_id) rang on rang.emp_id = e.id
            join net.rang r on r.rang = rang.max_rang
            left join (--черный список
                        select 
                        b.blacklist || 'black' id, 
                        emp.id emp_id, 
                        'Статья нарушения: ' || bt.name name, 
                        b.data_open, 
                        b.data_close, 
                        b.prim
                        from tehno.blacklist b
                        join tehno.blacklist_type bt on bt.id = b.shtraf
                        join net.employee emp on emp.contract = b.contract
                        where b.shtraf in (31, 32)
                        and b.data_open <= sysdate
                        and (b.data_close >= sysdate or b.data_close is null)
                        union all
                        select 
                        pe.id || 'excluded' id, 
                        pe.emp_id, 
                        'Исключен из программы: ' || pp.name name, 
                        pe.start_dt data_open,
                        to_date('01.01.3000') data_close,
                        pe.note prim
                        from net.prz_except pe
                        join net.pd_prog pp on pp.id = pe.prog_id
                        where pe.included = 0
                        and pe.prog_id != 6438347
                        and pe.start_dt <= sysdate) black on black.emp_id = e.id
            join net.pd_reward_status rs on rs.employee = e.id
            LEFT JOIN net.pd_prog p ON p.id = rs.prog AND p.id != 6438347
            LEFT JOIN net.pd_nominations n ON n.id = rs.nomination
            JOIN tehno.tovar t ON t.id = rs.gds_id
            JOIN net.employee e ON e.id = rs.employee AND e.d_end IS NULL
            LEFT JOIN test.cp_emp emp ON emp.id = rs.reward_user
            left join net.pd_employee_status emp_st on emp_st.employee_id = e.id
            
            where 1=1
            and e.id = :partner_id
            AND NOT EXISTS (SELECT NULL FROM net.pd_present_gds pg WHERE pg.gds_id = rs.gds_id)
            AND t.id IN (SELECT gds_id FROM net.pd_gds_reward_types WHERE reward_type_id = 1)
            $programsCondition
            $nominationsCondition
            $rewardStatusCondition
            $rewardsCondition
            $rewardWinDateCondition
            $rewardIssuedDateCondition
        ";

        $partner = $this->query($sql, [
            'partner_id'            => $request->partnerId,
            'reward_status_id'      => $request->rewardIssuanceState?->value,
            'program_id_list'       => $request->programIds,
            'nomination_id_list'    => $request->nominationIds,
            'reward_id_list'        => $request->rewardIds,
            'nomination_start_date' => $request->nominationStartDate,
            'nomination_end_date'   => $request->nominationEndDate,
            'reward_start_date'     => $request->rewardStartDate,
            'reward_end_date'       => $request->rewardEndDate,
        ],
            PartnersQueryRepositoryHelper::prepareParameterTypes()
        )->first();

        if (is_null($partner)) {
            throw new EntityNotFoundDatabaseException("не найден партнёр с id " . $request->partnerId);
        }

        return $partner;
    }

    public function buildCommonCondition(PartnerFullInfoRequest $request): array
    {
        $programsCondition = $request->programIds === [] ? "" : "and rs.prog in (:program_id_list)";
        $nominationsCondition = $request->nominationIds === [] ? "" : "and rs.nomination in (:nomination_id_list)";
        $rewardsCondition = $request->rewardIds === [] ? "" : "and rs.gds_id in (:reward_id_list)";
        $rewardStatusCondition = $request->rewardIssuanceState instanceof RewardIssuanceStateStatusType ? "and rs.is_reward = :reward_status_id" : "";
        $rewardWinDateCondition = PartnersQueryRepositoryHelper::resolveWinDateCondition($request->nominationStartDate, $request->nominationEndDate);
        $rewardIssuedDateCondition = PartnersQueryRepositoryHelper::resolveIssuedDateCondition($request->rewardStartDate, $request->rewardEndDate);

        return [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardStatusCondition,
            $rewardWinDateCondition,
            $rewardIssuedDateCondition,
        ];
    }
}
