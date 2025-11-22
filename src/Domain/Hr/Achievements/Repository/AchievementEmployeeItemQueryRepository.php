<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Repository;

use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use Database\ORM\QueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<AchievementEmployeeItem>
 */
class AchievementEmployeeItemQueryRepository extends QueryRepository
{
    protected string $entityName = AchievementEmployeeItem::class;

    protected const BASE_SQL = "SELECT
            ea.id,
            ea.receive_date,
            ea.add_date,
            ------------------------- employee
            ce.id cp_emp_id_id,
            ce.name cp_emp_id_name,
            ds.name cp_emp_id_response,
            ------------------------- achievement
            ach.id achievement_cards_id_id,
            ach.name achievement_cards_id_name,
            ach.description achievement_cards_id_description,
            ----------------------------------------- category
            cat.id achievement_cards_id_categories_id_id,
            cat.name achievement_cards_id_categories_id_name,
            cat.is_personal achievement_cards_id_categories_id_is_personal,
            cat.is_hidden achievement_cards_id_categories_id_is_hidden,
            ----------------------------------------- color
            color.id achievement_cards_id_categories_id_colors_id_id,
            color.url achievement_cards_id_categories_id_colors_id_url,
            color.file_id achievement_cards_id_categories_id_colors_id_file_id,
            ----------------------------------------- image
            image.id achievement_cards_id_image_library_id_id,
            image.name achievement_cards_id_image_library_id_name,
            image.cp_files_id achievement_cards_id_image_library_id_cp_files_id
        FROM test.cp_ea_employee_achievments ea
        JOIN test.cp_emp ce ON ce.id = ea.cp_emp_id AND ce.active = 'Y'
        LEFT JOIN test.cp_emp_state es ON es.employee = ce.id AND es.is_main = 1
             -- хак для получения только первой должности, если у пользователя их несколько
            AND es.id = (SELECT MIN(es2.id) FROM test.cp_emp_state es2 WHERE es2.employee = ce.id AND es2.is_main = 1)
        LEFT JOIN test.cp_depart_state ds ON ds.id = es.state
        JOIN TEST.CP_EA_ACHIEVEMENT_CARDS ach ON ach.id = ea.ACHIEVEMENT_CARDS_ID
        JOIN TEST.CP_EA_CATEGORIES cat ON ach.categories_id = cat.id
        JOIN TEST.CP_EA_COLORS color ON color.id = cat.colors_id
        JOIN TEST.CP_EA_IMAGE_LIBRARY image ON ach.image_library_id = image.id";

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getAll(): Enumerable
    {
        return $this->query(self::BASE_SQL);
    }

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getByAchievementIdWithEditor(int $achievementId): Enumerable
    {
        // добавляем поля для эдитора
        $baseSQL = preg_replace(
            '#^SELECT#',
            'SELECT le.id editor_id,
            le.name editor_name,
            le.name editor_response, ',
            self::BASE_SQL
        );

        $sql = $baseSQL . '
        left JOIN (
            SELECT
                log.id,
                log.log_cp_emp editor_id,
                log.log_ts,
                ROW_NUMBER() OVER (PARTITION BY log.id ORDER BY log.log_ts DESC) AS rn
            FROM test.cp_ea_employee_achievments_log log
        ) last_log ON last_log.id = ea.id AND last_log.rn = 1
        left JOIN test.CP_EMP le ON le.id = last_log.editor_id
        WHERE ach.id = :id';

        return $this->query($sql, [
            'id' => $achievementId,
        ]);
    }

    public function getById(int $id): ?AchievementEmployeeItem
    {
        $sql = self::BASE_SQL . ' WHERE ea.id = :id';
        return $this->query($sql, [
            'id' => $id,
        ])->first();
    }

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getEmployeeAchievements(int $userId): Enumerable
    {
        $sql = self::BASE_SQL . ' WHERE ce.id = :id';
        return $this->query($sql, [
            'id' => $userId,
        ]);
    }

    /** @return Enumerable<int, AchievementEmployeeItem> */
    public function getAchievementUnlockers(int $achievementId): Enumerable
    {
        $sql = self::BASE_SQL . ' WHERE ach.id = :id';
        return $this->query($sql, [
            'id' => $achievementId,
        ]);
    }

    public function employeeAchievementExistsInMonth(
        int $employeeId,
        int $achievementCardId,
        DateTimeImmutable $receiveDate,
        int $recordId
    ): bool {
        $sql = "SELECT id
            FROM test.cp_ea_employee_achievments ea
            WHERE ea.cp_emp_id = :employeeId
            AND ea.achievement_cards_id = :achievementCardId
            AND TO_CHAR(ea.receive_date, 'YYYY-MM') = :receiveDate
            AND id <> :recordId
            ";

        $result = $this->conn->query($sql, [
            'employeeId'        => $employeeId,
            'achievementCardId' => $achievementCardId,
            'receiveDate'       => $receiveDate->format('Y-m'),
            'recordId'          => $recordId,
        ]);

        return iterator_to_array($result) !== [];
    }
}
