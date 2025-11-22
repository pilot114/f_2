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
use App\Domain\Dit\Gifts\UseCase\DeleteCertificateUseCase;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->queryRepository = Mockery::mock(CertificatesQueryRepository::class);
    $this->commandRepository = Mockery::mock(CertificateCommandRepository::class);

    $this->useCase = new DeleteCertificateUseCase(
        $this->queryRepository,
        $this->commandRepository
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('successfully deletes certificate when no usages exist', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 1000.0, // Full amount remains
        certificateType: $certificateType,
        currency: $currency
    );

    $usages = new Collection([]); // No usages

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

    $this->commandRepository
        ->shouldReceive('deleteCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once();

    $result = $this->useCase->delete('CONTRACT001', 'CERT123');

    expect($result)->toBeTrue();
});

it('throws exception when certificate has usages', function (): void {
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

    // Create usage that prevents deletion
    $usage = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -200.0,
        sumRemains: 800.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usages = new Collection([$usage]);

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

    expect(fn () => $this->useCase->delete('CONTRACT001', 'CERT123'))
        ->toThrow(InvariantDomainException::class, 'возможно удаление сертификата только если не было начислений/списаний');
});

it('throws exception when certificate not found', function (): void {
    $this->queryRepository
        ->shouldReceive('getCertificate')
        ->with('CONTRACT001', 'CERT123')
        ->once()
        ->andThrow(new NotFoundHttpException('Certificate not found'));

    expect(fn () => $this->useCase->delete('CONTRACT001', 'CERT123'))
        ->toThrow(NotFoundHttpException::class);
});

it('throws exception when certificate has multiple usages', function (): void {
    $certificateType = new CertificateType(1, 'Test Type');
    $currency = new Currency(1, 'RUB');

    $certificate = new Certificate(
        id: 123,
        number: 'CERT123',
        partnerContract: 'CONTRACT001',
        denomination: 1000.0,
        sumRemains: 900.0,
        certificateType: $certificateType,
        currency: $currency
    );

    // Multiple usages
    $usage1 = new CertificateUsage(
        id: 1,
        certificateNumber: 'CERT123',
        value: -100.0,
        sumRemains: 900.0,
        headerId: 1,
        headerName: 'Автосписание'
    );

    $usage2 = new CertificateUsage(
        id: 2,
        certificateNumber: 'CERT123',
        value: 200.0,
        sumRemains: 1100.0,
        headerId: 2,
        headerName: 'Начисление'
    );

    $usages = new Collection([$usage1, $usage2]);

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

    expect(fn () => $this->useCase->delete('CONTRACT001', 'CERT123'))
        ->toThrow(InvariantDomainException::class, 'возможно удаление сертификата только если не было начислений/списаний');
});
