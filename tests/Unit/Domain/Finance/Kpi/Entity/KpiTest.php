<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiMetricHistory;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use DateTimeImmutable;

it('creates kpi with valid value', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
    );

    expect($kpi->getType())->toBe(KpiType::MONTHLY);
});

it('creates kpi with null value', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: null,
    );

    expect($kpi->isEmpty())->toBeTrue()
        ->and($kpi->isFilled())->toBeFalse();
});

it('creates kpi with NOT_FILLED special value', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 888,
    );

    expect($kpi->isFilled())->toBeFalse();
});

it('creates kpi with NOT_ASSIGNED special value', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 999,
    );

    expect($kpi->isFilled())->toBeFalse();
});

it('throws exception for invalid value', function (): void {
    expect(fn (): Kpi => new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 150,
    ))->toThrow(InvariantDomainException::class);
});

it('throws exception when billing month is not first day', function (): void {
    expect(fn (): Kpi => new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-15'),
        type: KpiType::MONTHLY,
        value: 50,
    ))->toThrow(InvariantDomainException::class);
});

it('sets value correctly', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
    );

    $kpi->setValue(75);

    expect($kpi->toArray(1)['value'])->toBe(75);
});

it('sets value calculated correctly', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
    );

    $kpi->setValueCalculated(60);

    expect($kpi->toArray(1, true)['valueCalculated'])->toBe(60);
});

it('checks if kpi is sent', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
        isSent: true,
    );

    expect($kpi->isSent())->toBeTrue();
});

it('gets billing month index', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-03-01'),
        type: KpiType::MONTHLY,
        value: 50,
    );

    expect($kpi->getBillingMonthIndex())->toBe(3);
});

it('gets billing month string', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
    );

    expect($kpi->getBillingMonthString())->toBeString()
        ->and($kpi->getBillingMonthString())->toContain('2025-01-01');
});

it('converts to array without valueCalculated', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
        valueCalculated: 80,
        isSent: false,
        sendDate: null,
    );

    $result = $kpi->toArray(empId: 123);

    expect($result['id'])->toBe(1)
        ->and($result['empId'])->toBe(123)
        ->and($result['type'])->toBe(KpiType::MONTHLY)
        ->and($result['value'])->toBe(75)
        ->and($result['isSent'])->toBeFalse()
        ->and($result)->not->toHaveKey('valueCalculated');
});

it('converts to array with valueCalculated', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
        valueCalculated: 80,
    );

    $result = $kpi->toArray(empId: 123, withValueCalculated: true);

    expect($result['valueCalculated'])->toBe(80);
});

it('formats value title for null', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: null,
    );

    expect($kpi->toArray(1)['valueTitle'])->toBe('—');
});

it('formats value title for NOT_FILLED', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 888,
    );

    expect($kpi->toArray(1)['valueTitle'])->toBe('Не заполняется');
});

it('formats value title for NOT_ASSIGNED', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 999,
    );

    expect($kpi->toArray(1)['valueTitle'])->toBe('Не назначен');
});

it('formats value title for numeric value', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
    );

    expect($kpi->toArray(1)['valueTitle'])->toBe('75');
});

it('gets metric history', function (): void {
    $history = new KpiMetricHistory(
        id: 1,
        name: 'Test',
        factualValue: 100,
        planValue: 120,
        weight: 0.5,
        calculationDescription: 'Test',
        rangesCount: 0,
        rangesDescription: '',
        unitType: UnitType::PIECES,
    );

    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 75,
    );

    $kpi->setMetricHistory([$history]);

    expect($kpi->getMetricHistory())->toHaveCount(1);
});

it('checks different kpi types', function (KpiType $type): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: $type,
        value: 75,
    );

    expect($kpi->getType())->toBe($type)
        ->and($kpi->toArray(1)['periodTitle'])->toBeString();
})->with([
    KpiType::MONTHLY,
    KpiType::BIMONTHLY,
    KpiType::QUARTERLY,
]);

it('checks if value is filled', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
    );

    expect($kpi->isFilled())->toBeTrue();
});

it('checks send date is formatted correctly', function (): void {
    $kpi = new Kpi(
        id: 1,
        billingMonth: new DateTimeImmutable('2025-01-01'),
        type: KpiType::MONTHLY,
        value: 50,
        isSent: true,
        sendDate: new DateTimeImmutable('2025-01-15'),
    );

    expect($kpi->toArray(1)['sendDate'])->toContain('2025-01-15');
});
