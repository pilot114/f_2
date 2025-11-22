<?php

declare(strict_types=1);

namespace App\System\Factory;

use Database\Connection\CpConnection;
use Database\ORM\CommandRepository;
use Database\ORM\DataMapperInterface;
use Database\ORM\QueryRepository;

/**
 * @template T of object
 */
readonly class RepositoryFactory
{
    /**
     * @param DataMapperInterface<T> $mapper
     */
    public function __construct(
        private CpConnection        $conn,
        private DataMapperInterface $mapper,
    ) {
    }

    /**
     * @param class-string<T> $entityName
     * @return CommandRepository<T>
     */
    public function command(string $entityName): CommandRepository
    {
        return new CommandRepository($this->conn, $this->mapper, $entityName);
    }

    /**
     * @param class-string<T> $entityName
     * @return QueryRepository<T>
     */
    public function query(string $entityName): QueryRepository
    {
        return new QueryRepository($this->conn, $this->mapper, $entityName);
    }
}
