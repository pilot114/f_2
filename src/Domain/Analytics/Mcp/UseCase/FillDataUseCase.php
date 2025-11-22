<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\UseCase;

use Database\Connection\WriteDatabaseInterface;

class FillDataUseCase
{
    public function __construct(
        private bool $dbIsProd,
        private WriteDatabaseInterface $conn,
    ) {
    }

    public function safeInsert(string $tableName, string $data): bool
    {
        if ($this->dbIsProd) {
            return false;
        }

        /** @var ?array $items */
        $items = json_decode($data, true);
        if ($items === null) {
            return false;
        }

        /** @var array $item */
        foreach ($items as $item) {
            $this->conn->insert($tableName, $item);
        }
        return true;
    }
}
