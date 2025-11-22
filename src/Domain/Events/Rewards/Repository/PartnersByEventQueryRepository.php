<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Common\DTO\FilterOption;
use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\DTO\PartnersByEventRequest;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\Registration;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Partner>
 */
class PartnersByEventQueryRepository extends QueryRepository
{
    protected string $entityName = Partner::class;

    /** @return Enumerable<int, Partner> */
    public function getPartnersByEvent(PartnersByEventRequest $request): Enumerable
    {
        $partnerIds = $this->getPartnerIdsOnPage($request);
        if ($partnerIds === []) {
            return EnumerableWithTotal::build();
        }

        [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $winDateCondition,
        ] = $this->buildCommonConditions($request);
        $sortDirection = mb_strtoupper($request->sortByName) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "select distinct
                cr.employee id,
                cr.name name,
                e.contract contract,
                case when e.familya = 'Y' then 1 else 0 end is_family,
                ----------------------------------
                c.tehno_id country_id,
                cntr.name  country_name,
                --------------------------------------
                emp_st.id status_id,
                emp_st.employee_id status_partner_id,
                emp_st.pd_status_id status_pd_status_id,
                emp_st.reward_count status_reward_count,
                emp_st.penalty_count status_penalty_count,
                net.calc_pd_employee_status (cr.employee, 1) actual_status,
                net.calc_pd_employee_status (cr.employee, 3) actual_reward_count,
                net.calc_pd_employee_status (cr.employee, 2) actual_penalty_count,
                -------------------------------------------
                reward.reward_status_id rewardissuancestate_id,            
                reward.result_id rewardissuancestate_calculation_result_id,            
                reward.prog_id rewardissuancestate_program_id,
                reward.prog_name rewardissuancestate_program_name,
                reward.nomination_id rewardissuancestate_nomination_id,
                reward.nomination_name rewardissuancestate_nomination_name,
                reward.is_reward rewardissuancestate_status,
                reward.win_dt rewardissuancestate_win_date,
                reward.cnt rewardissuancestate_count,
                reward.note rewardissuancestate_note,
                reward.present_id rewardissuancestate_reward_id,
                reward.present_name rewardissuancestate_reward_name,
                reward.product_id rewardissuancestate_reward_product_id,
                reward.status_id rewardissuancestate_reward_statuses_id,
                reward.status rewardissuancestate_reward_statuses_status,
                reward.status_country_id rewardissuancestate_reward_statuses_country_id,
                reward.status_country_name rewardissuancestate_reward_statuses_country_name

                from inet.celeb_registration cr
                join net.employee e on e.id = cr.employee and e.d_end is null
                join net.employee cok on cok.id = e.cok
                join net.country c on c.id = cok.country
                join tehno.country cntr on cntr.country = c.tehno_id
                left join (--список наград
                            select rs.id reward_status_id,
                                   rs.present_id result_id,
                                   rs.employee,
                                   pnp.family,
                                   rs.win_dt,
                                   rs.prog prog_id,
                                   pp.name prog_name,
                                   rs.nomination nomination_id,
                                   pn.name nomination_name,
                                   ppg.id present_id,
                                   ppg.gds_id product_id,
                                   t.name present_name,
                                   ppgcs.country_id status_country_id,
                                   c.name status_country_name,
                                   ppgcs.id status_id,
                                   ppgcs.status,
                                   rs.note,
                                   rs.cnt,
                                   rs.is_reward
                            from net.pd_reward_status rs
                            join net.pd_prog pp on pp.id = rs.prog and pp.id != 6438347
                            join net.pd_prog_group prog_grp on prog_grp.pd_prog_id = pp.id and prog_grp.pd_present_gds_id is null and prog_grp.pd_group_id != 3
                            join net.pd_nominations pn on pn.id = rs.nomination
                            join net.pd_nomination_presents pnp on pnp.nomination = pn.id
                            join net.pd_present_gds ppg on ppg.nomination_pr = pnp.id and ppg.gds_id = rs.gds_id
                            join net.pd_present_gds_country_status ppgcs on ppgcs.pd_present_gds_id = ppg.id and ppgcs.status = 1
                            left join tehno.country c on c.country =  ppgcs.country_id
                            join tehno.tovar t on t.id = rs.gds_id
                            where 1=1
                            and rs.is_reward in (0, 2)
                            $programsCondition           
                            $nominationsCondition    
                            $rewardsCondition       
                            $winDateCondition
                            ) reward on reward.employee = e.id and reward.family=(case when e.familya='Y' then 1 else 0 end)
                            and reward.status_country_id = cntr.country
                left join net.pd_employee_status emp_st on emp_st.employee_id = e.id
                where 1=1
                and cr.celeb = :celeb_id
                and cr.del_dt is null
                and cr.employee in (:partner_id_list)
                order by cr.name $sortDirection, cr.employee
        ";

        return $this->query($sql, [
            'celeb_id'              => $request->eventId,
            'partner_id_list'       => $partnerIds,
            'program_id_list'       => $request->programIds,
            'nomination_id_list'    => $request->nominationIds,
            'reward_id_list'        => $request->rewardIds,
            'nomination_start_date' => $request->nominationStartDate,
            'nomination_end_date'   => $request->nominationEndDate,
        ], $this->prepareParameterTypes());
    }

    private function buildPartnersBaseSelect(PartnersByEventRequest $request): string
    {
        [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardWinDateCondition,
            $countryCondition,
            $penaltyCondition,
            $partnerStatusCondition
        ] = $this->buildCommonConditions($request);

        $hasRewardFilters = $programsCondition !== ''
            || $nominationsCondition !== ''
            || $rewardsCondition !== ''
            || $rewardWinDateCondition !== '';

        $existsCondition = '';
        if ($hasRewardFilters) {
            $existsCondition = "
                  and exists (
                       select 1
                       from net.pd_reward_status rs
                       join net.pd_prog pp on pp.id = rs.prog and pp.id != 6438347
                       join net.pd_prog_group prog_grp on prog_grp.pd_prog_id = pp.id and prog_grp.pd_present_gds_id is null and prog_grp.pd_group_id != 3
                       join net.pd_nominations pn on pn.id = rs.nomination
                       join net.pd_nomination_presents pnp on pnp.nomination = pn.id
                       join net.pd_present_gds ppg on ppg.nomination_pr = pnp.id and ppg.gds_id = rs.gds_id
                       join net.pd_present_gds_country_status ppgcs on ppgcs.pd_present_gds_id = ppg.id and ppgcs.status = 1
                       join tehno.country tc on tc.country =  ppgcs.country_id
                       where rs.employee = e.id
                       and pnp.family = (case when e.familya='Y' then 1 else 0 end)
                       and ppgcs.country_id = cntr.country
                       and rs.is_reward in (0, 2)
                       $programsCondition
                       $nominationsCondition
                       $rewardsCondition
                       $rewardWinDateCondition
                   )";
        }

        $searchCondition = '';
        if ($request->search !== '') {
            $searchCondition = " and (upper(cr.name) like '%'||:search||'%' or upper(e.contract) like '%'||:search||'%')";
        }

        return <<<HEREDOC
                select distinct 
                cr.employee as partner_id
                , cr.name   as partner_name
                
                from inet.celeb_registration cr
                join net.employee e on e.id = cr.employee and e.d_end is null
                join net.employee cok on cok.id = e.cok
                join net.country c on c.id = cok.country
                join tehno.country cntr on cntr.country = c.tehno_id
                where 1=1
                and cr.celeb = :celeb_id 
                and cr.del_dt is null
                $countryCondition
                $penaltyCondition
                $searchCondition
                $existsCondition
                $partnerStatusCondition 
            HEREDOC;
    }

    public function countPartnersByEvent(PartnersByEventRequest $request): int
    {
        $base = $this->buildPartnersBaseSelect($request);

        $totalSql = "select count(*) as cnt from ( {$base} )";

        $params = $this->prepareParams($request);
        $types = $this->prepareParameterTypes();

        $rows = iterator_to_array($this->conn->query($totalSql, $params, $types));
        return isset($rows[0]['cnt']) ? (int) $rows[0]['cnt'] : 0;
    }

    /** @return Enumerable<int, Registration> */
    public function getPartnersRegistrations(array $partnersIds, int $celebId): Enumerable
    {
        $sql = <<<HEREDOC
            select 
              cr.id	
            , cr.employee partner_id 
            , cr.reg_dt ticket_reg_date
            
            from inet.celeb_registration cr
            where 1=1
            and cr.celeb = :celeb_id 
            and cr.del_dt is null
            and cr.employee in (:partner_id_list)
            HEREDOC;

        $raw = $this->conn->query(
            $sql,
            [
                'partner_id_list' => $partnersIds,
                'celeb_id'        => $celebId,
            ],
            [
                'partner_id_list' => ParamType::ARRAY_INTEGER,
            ]
        );

        return $this->customDenormalizeToCollection($raw, Registration::class);
    }

    private function prepareParams(PartnersByEventRequest $request): array
    {
        $searchValue = $request->search === '' ? null : mb_strtoupper(trim($request->search));
        return [
            'celeb_id'              => $request->eventId,
            'country_id'            => $request->country instanceof FilterOption ? null : $request->country,
            'program_id_list'       => $request->programIds,
            'nomination_id_list'    => $request->nominationIds,
            'reward_id_list'        => $request->rewardIds,
            'nomination_start_date' => $request->nominationStartDate,
            'nomination_end_date'   => $request->nominationEndDate,
            'partner_status'        => $request->partnerStatus?->value,
            'search'                => $searchValue,
        ];
    }

    private function prepareParameterTypes(): array
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

    private function getPartnerIdsOnPage(PartnersByEventRequest $request): array
    {
        $page = max(1, $request->page);
        $perPage = max(1, $request->perPage);
        $offset = ($page - 1) * $perPage;

        $base = $this->buildPartnersBaseSelect($request);
        $partnerIdsSql = "select partner_id, partner_name from ( {$base} )";
        $sortDirection = mb_strtoupper($request->sortByName) === 'ASC' ? 'ASC' : 'DESC';

        $partnerIdsPaginatedSql = "
            select partner_id, partner_name
            from ( $partnerIdsSql )
            order by 2 $sortDirection, 1
            offset :offset rows fetch next :limit rows only
        ";

        $params = array_replace($this->prepareParams($request), [
            'offset' => $offset,
            'limit'  => $perPage,
        ]);

        $types = array_replace($this->prepareParameterTypes(), [
            'offset' => ParamType::INTEGER,
            'limit'  => ParamType::INTEGER,
        ]);

        $idsIterator = $this->conn->query($partnerIdsPaginatedSql, $params, $types);
        $ids = iterator_to_array($idsIterator);
        return array_map(static fn (array $row): int => (int) $row['partner_id'], $ids);
    }

    private function buildCommonConditions(PartnersByEventRequest $request): array
    {
        $programsCondition = $request->programIds === [] ? "" : "and rs.prog in (:program_id_list)";
        $nominationsCondition = $request->nominationIds === [] ? "" : "and rs.nomination in (:nomination_id_list)";
        $rewardsCondition = $request->rewardIds === [] ? "" : "and rs.gds_id in (:reward_id_list)";
        $rewardWinDateCondition = PartnersQueryRepositoryHelper::resolveWinDateCondition($request->nominationStartDate, $request->nominationEndDate);
        $countryCondition = $request->country instanceof FilterOption ? "" : "and cntr.country = :country_id";
        $penaltyCondition = $request->hasPenalty ? "and net.calc_pd_employee_status (cr.employee, 2) > 0" : "";
        $partnerStatusCondition = $request->partnerStatus instanceof PartnerStatusType ? "and net.calc_pd_employee_status (cr.employee, 1) = :partner_status" : "";

        return [
            $programsCondition,
            $nominationsCondition,
            $rewardsCondition,
            $rewardWinDateCondition,
            $countryCondition,
            $penaltyCondition,
            $partnerStatusCondition,
        ];
    }
}
