<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\Post;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Post>
 */
class PostQueryRepository extends QueryRepository
{
    protected string $entityName = Post::class;

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function getPostsWithDepartments(): array
    {
        $sql = <<<SQL
            select
              distinct d.id department_id,
              d.name department_name,
              r.id post_id,
              r.name post_name
            from
              test.cp_response r
              join test.cp_depart_state ds
                on ds.response = r.id
                and ds.current_employee is not null
              join test.cp_departament d
                on d.id = ds.depart
                and (d.for_kpi = 1 or d.for_kpi_s = 1)
                and d.is_closed = 0
        SQL;
        $raw = iterator_to_array($this->conn->query($sql));

        return array_map(static fn (array $x): array => [
            'departmentId'   => (int) $x['department_id'],
            'departmentName' => $x['department_name'],
            'postId'         => (int) $x['post_id'],
            'postName'       => $x['post_name'],
        ], $raw);
    }
}
