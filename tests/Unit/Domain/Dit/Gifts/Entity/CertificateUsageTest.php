<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\Entity;

use App\Domain\Dit\Gifts\Entity\CertificateUsage;
use DateTimeImmutable;

it('creates certificate usage with all fields', function (): void {
    $date = new DateTimeImmutable('2023-01-01T10:00:00Z');

    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание',
        commentary: 'Test comment',
        date: $date
    );

    expect($usage->id)->toBe(123);
    expect($usage->certificateNumber)->toBe('CERT123');
    expect($usage->value)->toBe(-100.0);
    expect($usage->sumRemains)->toBe(700.0);
    expect($usage->getHeaderId())->toBe(456);
    expect($usage->headerName)->toBe('Автосписание');
    expect($usage->commentary)->toBe('Test comment');
    expect($usage->date)->toBe($date);
});

it('creates certificate usage with minimal required fields', function (): void {
    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    expect($usage->commentary)->toBeNull();
    expect($usage->date)->toBeNull();
    expect($usage->isCanceled())->toBeFalse();
});

it('detects cancel operation correctly', function (): void {
    $cancelUsage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: 100.0, // positive value
        sumRemains: 800.0,
        headerId: 456,
        headerName: 'Автосписание' // header name is 'Автосписание'
    );

    $normalUsage = new CertificateUsage(
        id: 124,
        certificateNumber: 'CERT123',
        value: -100.0, // negative value
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    $addUsage = new CertificateUsage(
        id: 125,
        certificateNumber: 'CERT123',
        value: 100.0,
        sumRemains: 800.0,
        headerId: 456,
        headerName: 'Начисление' // different header name
    );

    expect($cancelUsage->isCancelOperation())->toBeTrue();
    expect($normalUsage->isCancelOperation())->toBeFalse();
    expect($addUsage->isCancelOperation())->toBeFalse();
});

it('detects write off correctly', function (): void {
    $writeOffUsage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0, // negative value
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание' // header name is 'Автосписание'
    );

    $cancelUsage = new CertificateUsage(
        id: 124,
        certificateNumber: 'CERT123',
        value: 100.0, // positive value
        sumRemains: 800.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    $addUsage = new CertificateUsage(
        id: 125,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Начисление' // different header name
    );

    expect($writeOffUsage->isAutoWriteOff())->toBeTrue();
    expect($cancelUsage->isAutoWriteOff())->toBeFalse();
    expect($addUsage->isAutoWriteOff())->toBeFalse();
});

it('handles canceled state correctly', function (): void {
    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    expect($usage->isCanceled())->toBeFalse();

    $usage->markAsCanceled();

    expect($usage->isCanceled())->toBeTrue();
});

it('converts to array correctly with all fields', function (): void {
    $date = new DateTimeImmutable('2023-01-01T10:00:00Z');

    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание',
        commentary: 'Test comment',
        date: $date
    );

    $array = $usage->toArray();

    expect($array)->toBe([
        'id'                   => 123,
        'certificateNumber'    => 'CERT123',
        'value'                => -100.0,
        'sumRemains'           => 700.0,
        'headerId'             => 456,
        'headerName'           => 'Автосписание',
        'date'                 => '2023-01-01T10:00:00+00:00',
        'commentary'           => 'Test comment',
        'isCanceled'           => false,
        'isInitialSumAddition' => false,
    ]);
});

it('converts to array correctly with null values', function (): void {
    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    $array = $usage->toArray();

    expect($array['date'])->toBeNull();
    expect($array['commentary'])->toBeNull();
    expect($array['isCanceled'])->toBeFalse();
});

it('converts to array correctly after being canceled', function (): void {
    $usage = new CertificateUsage(
        id: 123,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 456,
        headerName: 'Автосписание'
    );

    $usage->markAsCanceled();
    $array = $usage->toArray();

    expect($array['isCanceled'])->toBeTrue();
});
