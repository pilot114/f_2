<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\Response;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<Response>
 */
class ResponsesQueryRepository extends QueryRepository
{
    protected string $entityName = Response::class;
}
