<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Controller;

use App\Domain\Analytics\Mcp\UseCase\FillDataUseCase;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

#[McpTool(
    name: 'fill-data',
    description: 'Добавить данные в тестовую БД',
)]
readonly class FillDataController
{
    public function __construct(
        private FillDataUseCase $useCase,
    ) {
    }

    public function __invoke(
        #[Schema(description: 'Полное имя таблицы, например, test.users')]
        string $tableName,
        #[Schema(description: 'Данные добавляемых записей в формате JSON, например, [{"name": "John", "age": 30}]')]
        string $data,
    ): bool {
        return $this->useCase->safeInsert($tableName, $data);
    }
}
