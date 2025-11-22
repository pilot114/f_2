<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Comment>
 */
class CommentsQueryRepository extends QueryRepository
{
    protected string $entityName = Comment::class;

    public function getById(int $commentId): Comment
    {
        $sql = "
            select 
                c.id,
                c.personal_page_id,
                c.is_pinned,
                c.create_date,
                c.text,
                --------
                e.id create_cp_emp_id_id,
                e.name create_cp_emp_id_name
            from test.CP_MP_COMMENTS c
            inner join test.cp_emp e on e.id = c.create_cp_emp_id
            where c.id = :commentId
        ";

        $comment = $this->query($sql, [
            'commentId' => $commentId,
        ])->first();

        if (!$comment) {
            throw new EntityNotFoundDatabaseException("Не найден комментарий с id = {$commentId}");
        }

        return $comment;
    }
}
