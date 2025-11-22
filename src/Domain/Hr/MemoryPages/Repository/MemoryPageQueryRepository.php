<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<MemoryPage>
 */
class MemoryPageQueryRepository extends QueryRepository
{
    protected string $entityName = MemoryPage::class;

    public function getItem(GetMemoryPageRequest $request): MemoryPage
    {
        $sql = <<<SQL
                    SELECT
                    pp.id,
                    pp.birth_date,
                    pp.death_date,
                    pp.create_date,
                    pp.obituary,
                    pp.obituary_full,
                    -------------------
                    emp.id cp_emp_id_id,
                    emp.name cp_emp_id_name,
                    -----------------------
                    wp.id workperiods_id,
                    wp.personal_page_id workperiods_personal_page_id,
                    wp.start_date workperiods_start_date,
                    wp.end_date workperiods_end_date,
                    wp.achievements workperiods_achievements,
                    --------------------------------------
                    r.id workperiods_response_id_id,
                    r.name workperiods_response_id_name,
                    -------------------------------------
                    com.id comments_id,
                    com.personal_page_id comments_personal_page_id,
                    com.is_pinned comments_is_pinned,
                    com.create_date comments_create_date,
                    com.text comments_text,
                    e.id comments_create_cp_emp_id_id,
                    e.name comments_create_cp_emp_id_name
                    
                    FROM test.cp_mp_personal_pages pp
                    JOIN test.cp_emp emp ON emp.id = pp.cp_emp_id
                    JOIN test.cp_mp_work_periods wp ON wp.personal_page_id = pp.id
                    JOIN test.cp_response r ON r.id = wp.response_id
                    LEFT JOIN test.cp_mp_comments com ON com.personal_page_id = pp.id
                    LEFT JOIN test.cp_emp e ON e.id = com.create_cp_emp_id
                    
                    WHERE pp.id = :personalPageId
SQL;

        $memoryPage = $this->query($sql, [
            'personalPageId' => $request->id,
        ])->first();

        if (!$memoryPage) {
            throw new EntityNotFoundDatabaseException("Не найдена страница памяти с id = {$request->id}");
        }

        return $memoryPage;
    }
}
