<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\UseCase;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Entity\CertificateType;
use App\Domain\Dit\Gifts\Entity\CertificateUsage;
use App\Domain\Dit\Gifts\Entity\Currency;
use App\Domain\Dit\Gifts\Repository\CertificateCommandRepository;
use App\Domain\Dit\Gifts\Repository\CertificatesQueryRepository;
use App\Domain\Dit\Gifts\UseCase\ChangeCertificateBalanceUseCase;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->queryRepository = Mockery::mock(CertificatesQueryRepository::class);
    $this->commandRepository = Mockery::mock(CertificateCommandRepository::class);

    $this->useCase = new ChangeCertificateBalanceUseCase(
        $this->queryRepository,
        $this->commandRepository
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('successfully writes off amount when sufficient balance', function (): void {
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

    $updatedCertificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 700.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([]);

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->twice()
        ->andReturn($certificate, $updatedCertificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->twice()
        ->andReturn($usages);

    $this->commandRepository
        ->shouldReceive('writeOff')
        ->with(100.0, 'CONTRACT001', 'CERT123', 'test comment')
        ->once();

    $result = $this->useCase->writeOff(100.0, 'CONTRACT001', 'CERT123', 'test comment');

    expect($result)->toBe($updatedCertificate);
});

it('throws exception when insufficient balance for writeOff', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 50.0, // Less than amount to write off
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([]);

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once()
        ->andReturn($certificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    expect(fn () => $this->useCase->writeOff(100.0, 'CONTRACT001', 'CERT123', 'test comment'))
        ->toThrow(InvariantDomainException::class, 'Списание невозможно. Остаток меньше суммы списания');
});

it('throws exception when certificate not found for writeOff', function (): void {
    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once()
        ->andThrow(new NotFoundHttpException('Certificate not found'));

    expect(fn () => $this->useCase->writeOff(100.0, 'CONTRACT001', 'CERT123', 'test comment'))
        ->toThrow(NotFoundHttpException::class);
});

it('successfully adds sum when write off operation exists', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    // Create certificate with write-off usage
    $writeOffUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 900.0,
        headerId: 1,
        headerName: '1'
    );

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $updatedCertificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 1000.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([$writeOffUsage]);

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->twice()
        ->andReturn($certificate, $updatedCertificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->twice()
        ->andReturn($usages);

    $this->commandRepository
        ->shouldReceive('addSum')
        ->with(1200.0, 'CONTRACT001', 'CERT123', 'test comment')
        ->once();

    $result = $this->useCase->addSum(200.0, 'CONTRACT001', 'CERT123', 'test comment');

    expect($result)->toBe($updatedCertificate);
});

it('throws exception when no write off operations exist for addSum', function (): void {
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

    $usages = new Collection([]); // No usages, no write-off operations

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once()
        ->andReturn($certificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    expect(fn () => $this->useCase->addSum(200.0, 'CONTRACT001', 'CERT123', 'test comment'))
        ->toThrow(InvariantDomainException::class);
});

it('successfully cancels write off when latest usage is auto write off', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    // Create auto write-off usage as latest
    $autoWriteOffUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 900.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $updatedCertificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 1000.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([$autoWriteOffUsage]);

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->twice()
        ->andReturn($certificate, $updatedCertificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->twice()
        ->andReturn($usages);

    $this->commandRepository
        ->shouldReceive('cancelWriteOff')
        ->with('CONTRACT001', 'CERT123')
        ->once();

    $result = $this->useCase->cancelAutoWriteOff('CONTRACT001', 'CERT123');

    expect($result)->toBe($updatedCertificate);
});

it('throws exception when latest usage is not auto write off for cancelWriteOff', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    // Create add operation (not write-off)
    $addUsage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: 100.0,
        sumRemains: 1100.0,
        headerId: 1,
        headerName: 'Начисление'
    );

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 800.0,
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([$addUsage]);

    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once()
        ->andReturn($certificate);

    $this->queryRepository
        ->shouldReceive('getCertificatesUsages')
        ->with(['CERT123'])
        ->once()
        ->andReturn($usages);

    expect(fn () => $this->useCase->cancelAutoWriteOff('CONTRACT001', 'CERT123'))
        ->toThrow(InvariantDomainException::class);
});
