<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Comment>
 */
class CommentCommandRepository extends CommandRepository
{
    protected string $entityName = Comment::class;

    public function deleteAllComments(int $memoryPageId): void
    {
        $this->conn->delete(
            Comment::TABLE,
            [
                'PERSONAL_PAGE_ID' => $memoryPageId,
            ]
        );
    }
}
