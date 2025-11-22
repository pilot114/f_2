<?php

declare(strict_types=1);

namespace App\Tests\Datasets;

use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;

function createFile(int $id, string $collectionName): File
{
    return new File(
        id: $id,
        name: 'test.jpg',
        filePath: '/path/to/test.jpg',
        userId: 1,
        idInCollection: 1,
        collectionName: $collectionName,
        extension: 'jpg',
        lastEditedDate: new DateTimeImmutable(),
    );
}
