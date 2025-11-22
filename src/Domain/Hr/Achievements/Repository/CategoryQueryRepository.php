<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Repository;

use App\Domain\Hr\Achievements\Entity\Category;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Category>
 */
class CategoryQueryRepository extends QueryRepository
{
    protected string $entityName = Category::class;

    protected const BASE_SQL = "
        SELECT
        cat.id,
        cat.name,
        cat.is_personal,
        cat.is_hidden,
        ----------------------------------------- achievements
        ach.id achievements_id,
        ach.name achievements_name,
        ach.description achievements_description,
        (SELECT count(ach.id) from cp_ea_employee_achievments ea WHERE ea.achievement_cards_id = ach.id) achievements_user_count,
        ----------------------------------------- color
        color.id colors_id_id,
        color.url colors_id_url,
        color.file_id colors_id_file_id,
        ----------------------------------------- image
        image.id achievements_image_library_id_id,
        image.name achievements_image_library_id_name,
        image.cp_files_id achievements_image_library_id_cp_files_id
        FROM TEST.CP_EA_CATEGORIES cat
        LEFT JOIN TEST.CP_EA_ACHIEVEMENT_CARDS ach ON ach.categories_id = cat.id
        LEFT JOIN TEST.CP_EA_COLORS color ON color.id = cat.colors_id
        LEFT JOIN TEST.CP_EA_IMAGE_LIBRARY image ON ach.image_library_id = image.id
    ";

    public function getById(int $id): ?Category
    {
        $sql = self::BASE_SQL . 'WHERE cat.id = :id';

        return $this->query($sql, [
            'id' => $id,
        ])->first();
    }

    /**
     * @return Enumerable<int, Category>
     */
    public function getAll(): Enumerable
    {
        return $this->query(self::BASE_SQL);
    }
}
