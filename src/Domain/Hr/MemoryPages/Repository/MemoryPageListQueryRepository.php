<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageListRequest;
use App\Domain\Hr\MemoryPages\Entity\MemoryPageListItem;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends  QueryRepository<MemoryPageListItem>
 */
class MemoryPageListQueryRepository extends QueryRepository
{
    protected string $entityName = MemoryPageListItem::class;

    /**
     * @return Enumerable<int, MemoryPageListItem>
     */
    public function getList(GetMemoryPageListRequest $request): Enumerable
    {
        $condition = '';
        if ($request->search) {
            $condition = "where lower(emp.name) like '%' || lower(:search) || '%' or lower(r.name) like '%' || lower(:search) || '%'";
        }
        $sql = "
                    select
                    pp.id,
                    cf.id main_photo_id,
                    pp.obituary,
                    -------------------
                    emp.id employee_id,
                    emp.name employee_name,
                    -----------------------
                    r.id response_id,
                    r.name response_name,
                    -----------------------
                    (select count(com.id) from test.cp_mp_comments com where com.personal_page_id = pp.id) comments_count

                    from test.cp_mp_personal_pages pp
                    join test.cp_emp emp on emp.id = pp.cp_emp_id
                    join (select
                            wp.personal_page_id,
                            max(wp.start_date) over (partition by wp.personal_page_id) start_date,
                            max(wp.end_date) over (partition by wp.personal_page_id) end_date,
                            max(wp.response_id) over (partition by wp.personal_page_id) responce_id
                            from test.cp_mp_work_periods wp) work on work.personal_page_id = pp.id
                    join test.CP_RESPONSE  r on r.id = work.responce_id
                    join test.cp_files cf on cf.parentid = pp.id and cf.parent_tbl = '" . MemoryPagePhotoService::MAIN_IMAGE_COLLECTION . "'
                    $condition
                    order by pp.death_date desc
                    ";

        return $this->query($sql, [
            'search' => $request->search,
        ]);
    }
}
