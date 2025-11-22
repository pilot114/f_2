<?php

declare(strict_types=1);

use App\Common\Service\File\TempFileRegistry;
use App\Domain\Dit\Reporter\Entity\ReportField;
use App\Domain\Dit\Reporter\Service\ReporterExcel;
use App\Gateway\WriteExcelGateway;

beforeEach(function (): void {
    $this->writer = Mockery::mock(WriteExcelGateway::class);
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->service = new ReporterExcel($this->writer, $this->tempFileRegistry);
});

afterEach(function (): void {
    Mockery::close();
});

it('sets report name', function (): void {
    $reportName = 'Test Report';

    $result = $this->service->setName($reportName);

    expect($result)->toBe($this->service);
});

it('sets content with fields mapping', function (): void {
    $fields = [
        createReportField('field1', 'Field 1'),
        createReportField('field2', 'Field 2'),
    ];

    $rows = [
        [
            'field1' => 'value1',
            'field2' => 'value2',
        ],
        [
            'field1' => 'value3',
            'field2' => 'value4',
        ],
    ];

    // Expect writer to set title
    $this->writer->shouldReceive('setTitle')
        ->once()
        ->with('reporter экспорт')
        ->andReturnSelf();

    // Expect writer to set data for headers (first iteration)
    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['Field 1', 'Field 2'])
        ->andReturnSelf();

    // Expect writer to set data for each row
    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['value1', 'value2'], 'A2')
        ->andReturnSelf();

    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['value3', 'value4'], 'A3')
        ->andReturnSelf();

    // Expect columns and rows to be called for setDefaultConfig
    $this->writer->shouldReceive('columns')
        ->andThrow(new RuntimeException('OpenSpout does not support column iteration'));

    $this->writer->shouldReceive('indexToLetter')
        ->andReturnUsing(fn ($i): string => chr(65 + $i)); // A=0, B=1, etc

    $this->writer->shouldReceive('setAutoSize')
        ->andReturnSelf();

    $this->writer->shouldReceive('setHeader')
        ->once()
        ->with(Mockery::type('object'))
        ->andReturnSelf();

    $result = $this->service->setContent($rows, $fields);

    expect($result)->toBe($this->service);
});

it('handles empty rows', function (): void {
    $fields = [
        createReportField('field1', 'Field 1'),
    ];

    $rows = [];

    $this->writer->shouldReceive('setTitle')
        ->once()
        ->with('reporter экспорт')
        ->andReturnSelf();

    $this->writer->shouldReceive('setData')
        ->never();

    $this->writer->shouldReceive('columns')
        ->andThrow(new RuntimeException('OpenSpout does not support column iteration'));

    $this->writer->shouldReceive('indexToLetter')
        ->andReturnUsing(fn ($i): string => chr(65 + $i)); // A=0, B=1, etc

    $this->writer->shouldReceive('setAutoSize')
        ->andReturnSelf();

    $this->writer->shouldReceive('setHeader')
        ->once()
        ->andReturnSelf();

    $result = $this->service->setContent($rows, $fields);

    expect($result)->toBe($this->service);
});

function createReportField(string $fieldName, string $displayLabel): ReportField
{
    return new ReportField(
        fieldName: $fieldName,
        displayLabel: $displayLabel,
    );
}
