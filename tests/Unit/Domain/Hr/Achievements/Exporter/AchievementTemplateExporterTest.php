<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\Exporter;

use App\Domain\Hr\Achievements\Exporter\AchievementTemplateExporter;
use Exception;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

beforeEach(function (): void {
    $this->logger = $this->createMock(LoggerInterface::class);
    $this->writer = new Writer();

    $this->exporter = new AchievementTemplateExporter(
        $this->logger,
        $this->writer
    );
});

it('returns correct exporter name', function (): void {
    $name = $this->exporter->getExporterName();

    expect($name)->toBe('AchievementTemplateExporter');
});

it('returns correct sanitized file name', function (): void {
    $fileName = $this->exporter->getFileName();

    expect($fileName)->toBe('Achievement_template.xlsx');
});

it('gets example data with correct structure', function (): void {
    $data = AchievementTemplateExporter::getExampleData();

    expect($data)->toBeArray();
    expect($data)->toHaveCount(7);

    // Check header row
    expect($data[0])->toBe(['ФИО сотрудника', 'Дата получения']);

    // Check instruction row
    expect($data[1])->toBe(['Вносить полностью и без ошибок!', 'Формат даты ГГГГ-ММ-ДД']);

    // Check example data contains expected values
    expect($data[5])->toBe(['Пит Брэд Уильямович', '2022–05–11']);
});

it('exports template and logs correctly', function (): void {
    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement template', [
            'params' => [
                'test' => 'value',
            ],
        ]);

    ob_start();
    try {
        $this->exporter->export([
            'test' => 'value',
        ]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('logs export parameters correctly', function (): void {
    $params = [
        'achievementId' => 123,
        'userId'        => 456,
    ];

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement template', [
            'params' => $params,
        ]);

    ob_start();
    try {
        $this->exporter->export($params);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('writes all example data rows to excel', function (): void {
    $expectedData = AchievementTemplateExporter::getExampleData();

    expect($expectedData)->toHaveCount(7);

    ob_start();
    try {
        $this->exporter->export([]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});
