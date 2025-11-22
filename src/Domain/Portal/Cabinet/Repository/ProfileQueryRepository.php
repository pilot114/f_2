<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Profile>
 */
class ProfileQueryRepository extends QueryRepository
{
    protected string $entityName = Profile::class;

    public function getProfileByUserId(int $userId): Profile
    {
        $sql = <<<SQL
                     SELECT
                            emp.id,
                            emp.id user_id,
                            emp.name,
                            cea.birthday,
                            cea.hide_birthday as hide_birthday,
                            cel.emp1s snils,
                            worktime.id worktime_id,
                            worktime.emp_id worktime_emp_id,
                            worktime.time_start worktime_time_start,
                            worktime.time_end worktime_time_end,
                            worktime.timezone worktime_timezone,
                            ---------------------------
                            cf.id avatar_id,
                            cf.parentid avatar_parentid,
                            cf.fpath avatar_fpath,
                            cf.date_edit avatar_date_edit,
                            cf.name avatar_name,
                            cf.active avatar_active,
                            cf.is_on_static avatar_is_on_static,
                            cf.ext avatar_extension,
                            ---------------------------
                            emp.position_name,
                            emp.response_adv position_description,
                            ---------------------------
                            emp.email contacts_email,
                            emp.telegram contacts_telegram,
                            emp.office_phone_city contacts_phone,
                            ----------------------------
                            emp.work_address address_city,
                            ----------------------------
                            dep.id AS departments_id,
                            dep.name AS departments_name,
                            dep.idparent AS departments_parent_id
                        FROM
                            (
                                SELECT 
                                    empl.id,
                                    empl.name, 
                                    empl.email, 
                                    empl.telegram, 
                                    empl.office_phone_city,
                                    empl.work_address,
                                    empl.job_id,
                                    empl.response_adv,    
                                    coalesce(ds.name, jr.name) position_name,
                                    empl.iddepartament
                                FROM test.cp_emp empl
                                LEFT JOIN (
                                    SELECT 
                                        ces.employee,
                                        ces.state,
                                        ROW_NUMBER() OVER (PARTITION BY ces.employee ORDER BY ces.is_main DESC) AS rn
                                    FROM 
                                        test.CP_EMP_STATE ces
                                ) ces ON empl.id = ces.employee AND ces.rn = 1
                                LEFT JOIN test.cp_depart_state ds ON ds.id = ces.state
                                LEFT JOIN test.cp_emp_job_ref jr ON jr.id = empl.job_id
                        		WHERE empl.id = :userId
                            ) emp
                        CROSS JOIN
                            test.cp_departament dep
                        LEFT JOIN test.cp_emp_anketa cea on cea.idemp = emp.id
                        LEFT JOIN test.cp_files cf ON emp.id = cf.parentid AND cf.parent_tbl = :parentTable
                        LEFT JOIN test.cp_link_emp_1s cel ON cel.emp = :userId AND cf.parent_tbl = :parentTable
                        LEFT JOIN test.cp_emp_worktime worktime ON worktime.emp_id = emp.id
                        START WITH
                            dep.id = emp.iddepartament
                        CONNECT BY
                            PRIOR dep.idparent = dep.id
SQL;

        $profile = $this->query($sql, [
            'userId'      => $userId,
            'parentTable' => File::USERPIC_COLLECTION,
        ])->first();

        if ($profile === null) {
            throw new EntityNotFoundDatabaseException('Профиль пользователя не найден');
        }

        $passCard = $this->findPassCard($userId);
        $profile->setPassCard($passCard);

        return $profile;
    }

    private function findPassCard(int $userId): ?string
    {
        $passCard = $this->conn->procedure('card_rfid.cr_card_emp',
            [
                'i_emp_id' => $userId,
                'o_result' => null,
            ],
            [
                'o_result' => [ParamMode::OUT, ParamType::CURSOR],
            ]
        );

        return $passCard['o_result'][0]['barcode'] ?? null;
    }
}
