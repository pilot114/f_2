<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\DTO;

use App\Domain\Finance\Kpi\DTO\MetricTypeResponse;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;

it('creates metric type response with all fields', function (): void {
    $response = new MetricTypeResponse(
        id: 1,
        name: 'Sales Target',
        planType: PaymentPlanType::LINEAR,
        ranges: [[
            'min' => 0,
            'max' => 100,
        ]],
        metrics: [[
            'id'   => 1,
            'name' => 'Metric 1',
        ]],
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Sales Target')
        ->and($response->planType)->toBe(PaymentPlanType::LINEAR)
        ->and($response->ranges)->toHaveCount(1)
        ->and($response->metrics)->toHaveCount(1);
});

it('creates metric type response with empty ranges and metrics', function (): void {
    $response = new MetricTypeResponse(
        id: 1,
        name: 'Test Metric',
        planType: PaymentPlanType::RANGES,
    );

    expect($response->ranges)->toBeEmpty()
        ->and($response->metrics)->toBeEmpty();
});

it('handles linear plan type', function (): void {
    $response = new MetricTypeResponse(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
    );

    expect($response->planType)->toBe(PaymentPlanType::LINEAR);
});

it('handles ranges plan type', function (): void {
    $response = new MetricTypeResponse(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::RANGES,
    );

    expect($response->planType)->toBe(PaymentPlanType::RANGES);
});

it('handles multiple ranges', function (): void {
    $ranges = [
        [
            'min' => 0,
            'max' => 50,
        ],
        [
            'min' => 51,
            'max' => 100,
        ],
        [
            'min' => 101,
            'max' => 150,
        ],
    ];

    $response = new MetricTypeResponse(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::RANGES,
        ranges: $ranges,
    );

    expect($response->ranges)->toHaveCount(3);
});

it('handles multiple metrics', function (): void {
    $metrics = [
        [
            'id'   => 1,
            'name' => 'Metric 1',
        ],
        [
            'id'   => 2,
            'name' => 'Metric 2',
        ],
    ];

    $response = new MetricTypeResponse(
        id: 1,
        name: 'Test',
        planType: PaymentPlanType::LINEAR,
        metrics: $metrics,
    );

    expect($response->metrics)->toHaveCount(2);
});

it('handles cyrillic names', function (): void {
    $response = new MetricTypeResponse(
        id: 1,
        name: 'Целевые продажи',
        planType: PaymentPlanType::LINEAR,
    );

    expect($response->name)->toBe('Целевые продажи');
});
