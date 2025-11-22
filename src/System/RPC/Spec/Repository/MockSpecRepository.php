<?php

declare(strict_types=1);

namespace App\System\RPC\Spec\Repository;

use Database\Connection\CpConnection;
use InvalidArgumentException;
use PSX\OpenRPC\OpenRPC;

class MockSpecRepository
{
    public const MOCK_SPEC_ID = 1;
    public const MOCK_SPEC_TABLE = 'test.cp_back_specs';

    public function __construct(
        private readonly CpConnection $connection,
    ) {
    }

    public function getMockSpec(): string
    {
        $mockSpecRaw = $this->connection->query(
            'select cbs.SPECIFICATON as specification from test.cp_back_specs cbs where id = :id',
            [
                'id' => self::MOCK_SPEC_ID,
            ]
        )->current();

        return isset($mockSpecRaw['specification']) && is_string($mockSpecRaw['specification'])
            ? $mockSpecRaw['specification']
            : '';
    }

    public function saveMockSpec(OpenRPC $spec): bool
    {
        $specJson = json_encode($spec);

        if (!$specJson) {
            throw new InvalidArgumentException('Не удалось получить спецификацию мока');
        }

        return $this->connection->update(
            self::MOCK_SPEC_TABLE,
            [
                'specificaton' => $specJson,
            ],
            [
                'id' => self::MOCK_SPEC_ID,
            ]
        ) > 0;
    }
}
