<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Entity\CertificateType;
use App\Domain\Dit\Gifts\Entity\CertificateUsage;
use App\Domain\Dit\Gifts\Entity\Currency;
use ArrayIterator;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('creates certificate with all required fields', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $created = new DateTimeImmutable('2023-01-01');
    $expires = new DateTimeImmutable('2024-01-01');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency,
        headerId: 456,
        created: $created,
        expires: $expires
    );

    expect($certificate->id)->toBe(123);
    expect($certificate->number)->toBe('CERT123');
    expect($certificate->partnerContract)->toBe('CONTRACT001');
    expect($certificate->denomination)->toBe(1000.0);
    expect($certificate->sumRemains)->toBe(800.0);
    expect($certificate->certificateType)->toBe($certificateType);
    expect($certificate->currency)->toBe($currency);
    expect($certificate->headerId)->toBe(456);
    expect($certificate->created)->toBe($created);
    expect($certificate->expires)->toBe($expires);
});

it('creates certificate with minimal required fields', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    expect($certificate->headerId)->toBeNull();
    expect($certificate->created)->toBeNull();
    expect($certificate->expires)->toBeNull();
    expect($certificate->getUsages())->toBe([]);
});

it('sets usages correctly', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usage1 = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание',
        date: new DateTimeImmutable('2023-01-01')
    );

    $usage2 = new CertificateUsage(
        id: 2,
        certificateNumber: 'CERT123',
        value: 200.0,
        sumRemains: 900.0,
        headerId: 2,
        headerName: 'Начисление',
        date: new DateTimeImmutable('2023-01-02')
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('filter')->andReturnSelf();
    $usagesCollection->shouldReceive('first')->andReturn(null);
    $usagesCollection->shouldReceive('forget')->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->with('date')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$usage2, $usage1]);

    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $usage1,
            1 => $usage2,
        ]));

    $certificate->setUsages($usagesCollection);

    expect($certificate->getUsages())->toBe([$usage2, $usage1]);
});

it('throws exception when setUsages receives invalid usage type', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => 'invalid_usage',
        ]));

    expect(fn () => $certificate->setUsages($usagesCollection))
        ->toThrow(InvariantDomainException::class, 'в usages могут быть только экземпляры CertificateUsage');
});

it('handles cancel operations in setUsages', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $originalUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $cancelUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: 100.0,
        sumRemains: 800.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $originalUsage,
            1 => $cancelUsage,
        ]));

    $filterCollection = Mockery::mock(Collection::class);
    $filterCollection->shouldReceive('first')->andReturn($originalUsage);
    $usagesCollection->shouldReceive('filter')->andReturn($filterCollection);

    $usagesCollection->shouldReceive('forget')->with([1])->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->with('date')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$originalUsage]);

    $certificate->setUsages($usagesCollection);

    expect($originalUsage->isCanceled())->toBeTrue();
});

it('checks if write off operation exists', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    // No usages - no write off
    expect($certificate->isAutoWriteOffOperationExists())->toBeFalse();

    // Add write off usage
    $writeOffUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $writeOffUsage,
        ]));
    $usagesCollection->shouldReceive('filter')->andReturnSelf();
    $usagesCollection->shouldReceive('first')->andReturn(null);
    $usagesCollection->shouldReceive('forget')->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$writeOffUsage]);

    $certificate->setUsages($usagesCollection);

    expect($certificate->isAutoWriteOffOperationExists())->toBeTrue();
});

it('checks if latest usage is auto write off', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    // No usages
    expect($certificate->isLatestUsageAutoWriteOff())->toBeFalse();

    // Add auto write off usage as latest
    $autoWriteOffUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $autoWriteOffUsage,
        ]));
    $usagesCollection->shouldReceive('filter')->andReturnSelf();
    $usagesCollection->shouldReceive('first')->andReturn(null);
    $usagesCollection->shouldReceive('forget')->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$autoWriteOffUsage]);

    $certificate->setUsages($usagesCollection);

    expect($certificate->isLatestUsageAutoWriteOff())->toBeTrue();
});

it('checks if certificate can be deleted', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    // No usages - can be deleted
    expect($certificate->canBeDeleted())->toBeTrue();

    // Add usage - cannot be deleted
    $usage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: 700.0,
        sumRemains: 700.0,
        headerId: 1311,
        headerName: '1311',
        commentary: 'первичное начисление'
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $usage,
        ]));
    $usagesCollection->shouldReceive('filter')->andReturnSelf();
    $usagesCollection->shouldReceive('first')->andReturn(null);
    $usagesCollection->shouldReceive('forget')->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$usage]);

    $certificate->setUsages($usagesCollection);

    expect($certificate->canBeDeleted())->toBeTrue();
});

it('calculates new denomination correctly', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    expect($certificate->calculateNewDenomination(200.0))->toBe(1200.0);
    expect($certificate->calculateNewDenomination(-100.0))->toBe(900.0);
});

it('converts to array correctly', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');
    $created = new DateTimeImmutable('2023-01-01T10:00:00Z');
    $expires = new DateTimeImmutable('2024-01-01T10:00:00Z');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency,
        headerId: 456,
        created: $created,
        expires: $expires
    );

    $usage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usagesCollection = Mockery::mock(Collection::class);
    $usagesCollection->shouldReceive('getIterator')
        ->andReturn(new ArrayIterator([
            0 => $usage,
        ]));
    $usagesCollection->shouldReceive('filter')->andReturnSelf();
    $usagesCollection->shouldReceive('first')->andReturn(null);
    $usagesCollection->shouldReceive('forget')->andReturnSelf();
    $usagesCollection->shouldReceive('sortByDesc')->andReturnSelf();
    $usagesCollection->shouldReceive('values')->andReturnSelf();
    $usagesCollection->shouldReceive('toArray')->andReturn([$usage]);

    $certificate->setUsages($usagesCollection);

    $array = $certificate->toArray();

    expect($array)->toBe([
        'id'              => 123,
        'number'          => 'CERT123',
        'headerId'        => 456,
        'partnerContract' => 'CONTRACT001',
        'denomination'    => 1000.0,
        'sumRemains'      => 800.0,
        'created'         => '2023-01-01T10:00:00+00:00',
        'expires'         => '2024-01-01T10:00:00+00:00',
        'type'            => [
            'id'   => 1,
            'name' => 'Test Type',
        ],
        'currency' => [
            'id'   => 1,
            'logo' => 'RUB',
        ],
        'usages' => [$usage->toArray()],
    ]);
});
