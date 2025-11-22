<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\UseCase;

use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Entity\CertificateType;
use App\Domain\Dit\Gifts\Entity\CertificateUsage;
use App\Domain\Dit\Gifts\Entity\Currency;
use App\Domain\Dit\Gifts\Repository\CertificatesQueryRepository;
use App\Domain\Dit\Gifts\UseCase\GetCertificatesListUseCase;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->queryRepository = Mockery::mock(CertificatesQueryRepository::class);

    $this->useCase = new GetCertificatesListUseCase(
        $this->queryRepository
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns certificates with usages correctly grouped', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate1 = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $certificate2 = new Certificate(
        id: 124,
        number: 'CERT124',
        partnerContract: 'CONTRACT001',
        denomination: 2000.0,
        sumRemains: 1500.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usage1 = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -200.0,
        sumRemains: 800.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usage2 = new CertificateUsage(
        id: 2,
        certificateNumber: 'CERT124',
        value: -500.0,
        sumRemains: 1500.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $certificates = new Collection([$certificate1, $certificate2]);
    $usages = new Collection([$usage1, $usage2]);

    $this->queryRepository
        ->shouldReceive('getCertificatesList')
        ->with('search_term')
        ->once()
        ->andReturn($certificates);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123', 'CERT124'])
        ->once()
        ->andReturn($usages);

    $result = $this->useCase->getCertificatesList('search_term');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(2);

    // Verify usages were set correctly on each certificate
    $resultArray = $result->toArray();
    expect($resultArray[0]->getUsages())->toHaveCount(1);
    expect($resultArray[1]->getUsages())->toHaveCount(1);
    expect($resultArray[0]->getUsages()[0]->certificateNumber)->toBe('CERT123');
    expect($resultArray[1]->getUsages()[0]->certificateNumber)->toBe('CERT124');
});

it('handles certificates with no usages', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 1000.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $certificates = new Collection([$certificate]);
    $usages = new Collection([]); // No usages

    $this->queryRepository
        ->shouldReceive('getCertificatesList')
        ->with('search_term')
        ->once()
        ->andReturn($certificates);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    $result = $this->useCase->getCertificatesList('search_term');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(1);

    $resultArray = $result->toArray();
    expect($resultArray[0]->getUsages())->toBe([]);
});

it('handles empty certificates list', function (): void {
    $certificates = new Collection([]);
    $usages = new Collection([]);

    $this->queryRepository
        ->shouldReceive('getCertificatesList')
        ->with('search_term')
        ->once()
        ->andReturn($certificates);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with([])
        ->once()
        ->andReturn($usages);

    $result = $this->useCase->getCertificatesList('search_term');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(0);
});

it('correctly handles multiple usages per certificate', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 700.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usage1 = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -200.0,
        sumRemains: 800.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usage2 = new CertificateUsage(
        id: 2,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 700.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $certificates = new Collection([$certificate]);
    $usages = new Collection([$usage1, $usage2]);

    $this->queryRepository
        ->shouldReceive('getCertificatesList')
        ->with('search_term')
        ->once()
        ->andReturn($certificates);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    $result = $this->useCase->getCertificatesList('search_term');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(1);

    $resultArray = $result->toArray();
    expect($resultArray[0]->getUsages())->toHaveCount(2);
    expect($resultArray[0]->getUsages()[0]->certificateNumber)->toBe('CERT123');
    expect($resultArray[0]->getUsages()[1]->certificateNumber)->toBe('CERT123');
});

it('handles usages for certificates not in the list (orphan usages)', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 1000.0,
        certificateType: $certificateType,
        currency: $currency
    );

    // Usage for a different certificate that's not in the list
    $orphanUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT999',
        value: -100.0,
        sumRemains: 900.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $certificates = new Collection([$certificate]);
    $usages = new Collection([$orphanUsage]);

    $this->queryRepository
        ->shouldReceive('getCertificatesList')
        ->with('search_term')
        ->once()
        ->andReturn($certificates);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    $result = $this->useCase->getCertificatesList('search_term');

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(1);

    // Certificate should have no usages since the usage is for a different certificate
    $resultArray = $result->toArray();
    expect($resultArray[0]->getUsages())->toBe([]);
});
