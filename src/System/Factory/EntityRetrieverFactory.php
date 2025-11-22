<?php

declare(strict_types=1);

namespace App\System\Factory;

use Database\Connection\CpConnection;
use Database\Schema\EntityRetriever;
use Database\Schema\ReadOnlySchemaManager;

class EntityRetrieverFactory
{
    public function get(CpConnection $conn): EntityRetriever
    {
        return new EntityRetriever(new ReadOnlySchemaManager($conn));
    }
}
