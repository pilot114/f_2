<?php

declare(strict_types=1);

it('get kpi history by controller', function (): void {
    $result = testRpcCall('finance.kpi.getHistory', [
        'empId' => 4026,
    ]);

    expect($result['result']['items'])->toBeArray()
        ->and($result['result']['total'])->toBeInt();
});
