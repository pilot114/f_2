<?php

declare(strict_types=1);

// для ручного тестирования
it('manual test call', function (): void {
    $result = testRpcCall('partners.saleStructure.geSaleStructure', [
        'contract' => '3278700',
        'from'     => '2025-05',
        'till'     => '2025-06',
    ]);
    dump($result);
});
