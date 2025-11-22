<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Repository;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<File>
 */
class FileQueryRepository extends QueryRepository
{
    protected string $entityName = File::class;

    public function fileExists(string $filePath): bool
    {
        return $this->count([
            'fpath'        => $filePath,
            'is_on_static' => 1,
        ]) > 0;
    }

    public function getNextIdByCollectionName(string $collectionName): int
    {
        $last = $this->conn->max('test.cp_files', 'parentid', [
            'parent_tbl' => $collectionName,
        ]);
        return is_float($last) ? intval($last) + 1 : 1;
    }
}
