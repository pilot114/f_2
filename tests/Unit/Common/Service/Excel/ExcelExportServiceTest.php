<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\Excel;

use App\Common\Service\Excel\ExcelExportService;
use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use App\Domain\Portal\Excel\Factory\ExcelExporterFactory;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function (): void {
    $this->exporterFactory = Mockery::mock(ExcelExporterFactory::class);
    $this->service = new ExcelExportService($this->exporterFactory);
});

afterEach(function (): void {
    Mockery::close();
});

it('creates StreamedResponse with correct headers', function (): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('test.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with('test-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('test-exporter', [
        'param' => 'value',
    ]);

    // Assert
    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))->toBe(ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="test.xlsx"');
});

it('creates correct response for different parameters', function (): void {
    // Arrange
    $params = [
        'startDate' => '2025-01-01',
        'endDate'   => '2025-12-31',
        'filter'    => 'active',
    ];

    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('report.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with('report-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('report-exporter', $params);

    // Assert
    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('creates exporter with correct name', function (): void {
    // Arrange
    $exporterName = 'kpi-exporter';
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('kpi.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with($exporterName)
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export($exporterName, []);

    // Assert
    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('uses filename from exporter', function (): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('custom-filename.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with('test-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('test-exporter', []);

    // Assert
    expect($response->headers->get('Content-Disposition'))->toContain('custom-filename.xlsx');
});

it('creates different exporters for different names', function (string $exporterName, string $fileName): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn($fileName);

    $this->exporterFactory->shouldReceive('create')
        ->with($exporterName)
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export($exporterName, []);

    // Assert
    expect($response->headers->get('Content-Disposition'))->toContain($fileName);
})->with([
    ['achievements-exporter', 'achievements.xlsx'],
    ['kpi-metrics-exporter', 'kpi-metrics.xlsx'],
    ['loans-exporter', 'loans.xlsx'],
]);

it('handles empty parameters', function (): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('empty.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with('empty-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('empty-exporter', []);

    // Assert
    expect($response)->toBeInstanceOf(StreamedResponse::class);
});

it('sets attachment disposition', function (): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('test.xlsx');

    $this->exporterFactory->shouldReceive('create')
        ->with('test-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('test-exporter', []);

    // Assert
    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toStartWith(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
});

it('is readonly class', function (): void {
    // Arrange
    $reflection = new ReflectionClass(ExcelExportService::class);

    // Assert
    expect($reflection->isReadOnly())->toBeTrue();
});

it('executes export callback in StreamedResponse', function (): void {
    // Arrange
    $exporter = Mockery::mock(AbstractExporter::class);
    $exporter->shouldReceive('getFileName')->andReturn('test.xlsx');
    $exporter->shouldReceive('export')
        ->with([
            'param' => 'value',
        ])
        ->once();

    $this->exporterFactory->shouldReceive('create')
        ->with('test-exporter')
        ->once()
        ->andReturn($exporter);

    // Act
    $response = $this->service->export('test-exporter', [
        'param' => 'value',
    ]);

    // Execute the callback by sending the response
    ob_start();
    $response->sendContent();
    ob_end_clean();

    // Assert - the export() method should have been called
    expect($response)->toBeInstanceOf(StreamedResponse::class);
});
