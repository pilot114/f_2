<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\Exporter;

use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemResponse;
use App\Domain\Hr\Achievements\DTO\AchievementResponse;
use App\Domain\Hr\Achievements\DTO\CategoryResponse;
use App\Domain\Hr\Achievements\DTO\EmployeeResponse;
use App\Domain\Hr\Achievements\DTO\ImageResponse;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Exporter\AchievementStructureExporter;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Collection;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

beforeEach(function (): void {
    $this->logger = $this->createMock(LoggerInterface::class);
    $this->repository = $this->createMock(AchievementEmployeeItemQueryRepository::class);
    $this->writer = new Writer();

    $this->exporter = new AchievementStructureExporter(
        $this->logger,
        $this->repository,
        $this->writer
    );
});

it('returns correct exporter name', function (): void {
    $name = $this->exporter->getExporterName();

    expect($name)->toBe('AchievementStructureExporter');
});

it('returns correct sanitized file name', function (): void {
    $fileName = $this->exporter->getFileName();

    expect($fileName)->toBe('Achievement_structure.xlsx');
});

it('logs export parameters correctly when exporting with empty data', function (): void {
    $this->repository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn(new Collection([]));

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement structure', [
            'params' => [],
        ]);

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

it('calls repository method correctly', function (): void {
    $receiveDate = new DateTimeImmutable('2022-05-15');

    $employeeResponse = new EmployeeResponse(1, 'John Doe', 'Senior Developer');
    $categoryResponse = new CategoryResponse(1, 'Technical Skills', false, false);
    $imageResponse = new ImageResponse(1, 'Test Image', 'https://example.com/image.png');
    $achievementResponse = new AchievementResponse(1, 'PHP Expert', 'Expert PHP developer', $categoryResponse, $imageResponse, 5);

    $addedDate = new DateTimeImmutable('2022-05-15 10:30:00');
    $itemResponse = new AchievementEmployeeItemResponse(
        1,
        $receiveDate,
        $addedDate,
        $employeeResponse,
        $achievementResponse
    );

    $achievementItem = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem
        ->expects($this->once())
        ->method('toAchievementEmployeeItemResponse')
        ->willReturn($itemResponse);

    $this->repository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn(new Collection([$achievementItem]));

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement structure', [
            'params' => [
                'test' => 'param',
            ],
        ]);

    ob_start();
    try {
        $this->exporter->export([
            'test' => 'param',
        ]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('handles multiple achievement items', function (): void {
    $receiveDate1 = new DateTimeImmutable('2022-05-15');
    $receiveDate2 = new DateTimeImmutable('2022-06-20');

    $employeeResponse1 = new EmployeeResponse(1, 'John Doe', 'Senior Developer');
    $employeeResponse2 = new EmployeeResponse(2, 'Jane Smith', 'Team Lead');

    $categoryResponse = new CategoryResponse(1, 'Technical Skills', false, false);

    $imageResponse = new ImageResponse(1, 'Test Image', 'https://example.com/image.png');
    $achievementResponse1 = new AchievementResponse(1, 'PHP Expert', 'Expert PHP developer', $categoryResponse, $imageResponse, 5);
    $achievementResponse2 = new AchievementResponse(2, 'React Expert', 'Expert React developer', $categoryResponse, $imageResponse, 3);

    $addedDate1 = new DateTimeImmutable('2022-05-15 10:30:00');
    $addedDate2 = new DateTimeImmutable('2022-06-20 14:45:00');
    $itemResponse1 = new AchievementEmployeeItemResponse(1, $receiveDate1, $addedDate1, $employeeResponse1, $achievementResponse1);
    $itemResponse2 = new AchievementEmployeeItemResponse(2, $receiveDate2, $addedDate2, $employeeResponse2, $achievementResponse2);

    $achievementItem1 = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem1->method('toAchievementEmployeeItemResponse')->willReturn($itemResponse1);

    $achievementItem2 = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem2->method('toAchievementEmployeeItemResponse')->willReturn($itemResponse2);

    $this->repository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn(new Collection([$achievementItem1, $achievementItem2]));

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
