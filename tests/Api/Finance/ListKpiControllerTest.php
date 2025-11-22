<?php

declare(strict_types=1);

it('get kpi list by controller', function (): void {
    $result = testRpcCall('finance.kpi.getList', [
        'empId' => 4026,
        'q'     => null,
    ]);

    expect($result['result']['items'])->toBeArray()
        ->and($result['result']['total'])->toBeInt();
});
