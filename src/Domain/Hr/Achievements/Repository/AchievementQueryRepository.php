<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Repository;

use App\Domain\Hr\Achievements\Entity\Achievement;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Achievement>
 */
class AchievementQueryRepository extends QueryRepository
{
    protected string $entityName = Achievement::class;

    protected const BASE_SQL = "SELECT
        ach.id id,
        ach.name name,
        ach.description description,
        ----------------------------------------- category
        cat.id categories_id_id,
        cat.name categories_id_name,
        cat.is_personal categories_id_is_personal,
        cat.is_hidden categories_id_is_hidden,
        ----------------------------------------- image
        image.id image_library_id_id,
        image.name image_library_id_name,
        image.cp_files_id image_library_id_cp_files_id,
        (SELECT count(ach.id) from cp_ea_employee_achievments ea WHERE ea.achievement_cards_id = ach.id) user_count,
        ----------------------------------------- color
        color.id categories_id_colors_id_id,
        color.url categories_id_colors_id_url,
        color.file_id categories_id_colors_id_file_id
        FROM TEST.CP_EA_ACHIEVEMENT_CARDS ach
        JOIN TEST.CP_EA_CATEGORIES cat ON ach.categories_id = cat.id
        JOIN TEST.CP_EA_IMAGE_LIBRARY image ON ach.image_library_id = image.id
        JOIN TEST.CP_EA_COLORS color ON color.id = cat.colors_id";

    /** @return Enumerable<int, Achievement> */
    public function getList(): Enumerable
    {
        return $this->query(self::BASE_SQL);
    }

    public function getById(int $id): ?Achievement
    {
        $sql = self::BASE_SQL . ' WHERE ach.id = :id';
        return $this->query($sql, [
            'id' => $id,
        ])->first();
    }

    public function nameExist(string $name): bool
    {
        return $this->conn->exist('test.cp_ea_achievement_cards', [
            'name' => $name,
        ]);
    }
}
