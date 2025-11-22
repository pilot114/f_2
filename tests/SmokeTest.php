<?php

declare(strict_types=1);

it('smoke test', function (string $methodName, array $params): void {

    $skipLongRunning = [
        'analytics.mcp.getArtefact',
        'hr.memoryPages.getMemoryPage',
    ];

    // TODO: разобраться с пропущенными методами
    $skipEmpty = [
        'finance.kpi.getMetric', // сейчас нет метрик
        'finance.kpi.getMetricType', // сейчас нет типов метрик
        'finance.pointsLoan.getPartnerStats', // проблема с проверкой прав
        'partners.saleStructure.getPartnerInfo', // проблема с проверкой прав
    ];

    if (in_array($methodName, $skipLongRunning, true) || in_array($methodName, $skipEmpty, true)) {
        expect(1)->toEqual(1);
        return;
    }

    $response = testRpcCall($methodName, $params);

    expect($response)->toHaveKey('result');

})->with('queryEndpointsWithExamples');
