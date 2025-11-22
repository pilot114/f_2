<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\Exporter;

use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemWithEditorResponse;
use App\Domain\Hr\Achievements\DTO\AchievementResponse;
use App\Domain\Hr\Achievements\DTO\CategoryResponse;
use App\Domain\Hr\Achievements\DTO\EmployeeResponse;
use App\Domain\Hr\Achievements\DTO\ImageResponse;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Exporter\AchievementUserListExporter;
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

    $this->exporter = new AchievementUserListExporter(
        $this->writer,
        $this->logger,
        $this->repository
    );
});

it('returns correct exporter name', function (): void {
    $name = $this->exporter->getExporterName();

    expect($name)->toBe('AchievementUnlockersExporter');
});

it('returns correct sanitized file name', function (): void {
    $fileName = $this->exporter->getFileName();

    expect($fileName)->toBe('Achievement_unlocked.xlsx');
});

it('exports user list with empty data and logs correctly', function (): void {
    $achievementId = 123;

    $this->repository
        ->expects($this->once())
        ->method('getByAchievementIdWithEditor')
        ->with($achievementId)
        ->willReturn(new Collection([]));

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement unlocked', [
            'params' => [
                'achievementId' => $achievementId,
            ],
        ]);

    ob_start();
    try {
        $this->exporter->export([
            'achievementId' => $achievementId,
        ]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('calls repository and logger correctly with achievement data', function (): void {
    $achievementId = 456;
    $receiveDate = new DateTimeImmutable('2022-05-15 10:30:00');
    $addedDate = new DateTimeImmutable('2022-05-15 10:30:00');

    $employeeResponse = new EmployeeResponse(1, 'John Doe', 'Senior Developer');
    $editorResponse = new EmployeeResponse(2, 'Admin User', 'HR Manager');
    $categoryResponse = new CategoryResponse(1, 'Technical Skills', false, false);
    $imageResponse = new ImageResponse(1, 'Test Image', 'https://example.com/image.png');
    $achievementResponse = new AchievementResponse(1, 'PHP Expert', 'Expert PHP developer', $categoryResponse, $imageResponse, 5);

    $itemResponse = new AchievementEmployeeItemWithEditorResponse(
        1,
        $receiveDate,
        $addedDate,
        $employeeResponse,
        $achievementResponse,
        $editorResponse
    );

    $achievementItem = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem
        ->expects($this->once())
        ->method('toAchievementEmployeeItemWithEditorResponse')
        ->willReturn($itemResponse);

    $this->repository
        ->expects($this->once())
        ->method('getByAchievementIdWithEditor')
        ->with($achievementId)
        ->willReturn(new Collection([$achievementItem]));

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement unlocked', [
            'params' => [
                'achievementId' => $achievementId,
            ],
        ]);

    ob_start();
    try {
        $this->exporter->export([
            'achievementId' => $achievementId,
        ]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('handles multiple users correctly', function (): void {
    $achievementId = 789;
    $receiveDate1 = new DateTimeImmutable('2022-05-15 10:30:00');
    $receiveDate2 = new DateTimeImmutable('2022-06-20 14:45:00');
    $addedDate1 = new DateTimeImmutable('2022-05-15 10:30:00');
    $addedDate2 = new DateTimeImmutable('2022-06-20 14:45:00');

    $employeeResponse1 = new EmployeeResponse(1, 'John Doe', 'Senior Developer');
    $employeeResponse2 = new EmployeeResponse(2, 'Jane Smith', 'Team Lead');
    $editorResponse = new EmployeeResponse(3, 'Admin User', 'HR Manager');

    $categoryResponse = new CategoryResponse(1, 'Technical Skills', false, false);
    $imageResponse = new ImageResponse(1, 'Test Image', 'https://example.com/image.png');
    $achievementResponse = new AchievementResponse(1, 'PHP Expert', 'Expert PHP developer', $categoryResponse, $imageResponse, 5);

    $itemResponse1 = new AchievementEmployeeItemWithEditorResponse(1, $receiveDate1, $addedDate1, $employeeResponse1, $achievementResponse, $editorResponse);
    $itemResponse2 = new AchievementEmployeeItemWithEditorResponse(2, $receiveDate2, $addedDate2, $employeeResponse2, $achievementResponse, $editorResponse);

    $achievementItem1 = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem1->method('toAchievementEmployeeItemWithEditorResponse')->willReturn($itemResponse1);

    $achievementItem2 = $this->createMock(AchievementEmployeeItem::class);
    $achievementItem2->method('toAchievementEmployeeItemWithEditorResponse')->willReturn($itemResponse2);

    $this->repository
        ->expects($this->once())
        ->method('getByAchievementIdWithEditor')
        ->with($achievementId)
        ->willReturn(new Collection([$achievementItem1, $achievementItem2]));

    ob_start();
    try {
        $this->exporter->export([
            'achievementId' => $achievementId,
        ]);
        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    } catch (Exception $e) {
        // Expected - can't actually write to browser in test environment
        expect($e->getMessage())->toContain('Cannot modify header information');
    } finally {
        ob_end_clean();
    }
});

it('handles string achievement id parameter', function (): void {
    $achievementId = '456';

    $this->repository
        ->expects($this->once())
        ->method('getByAchievementIdWithEditor')
        ->with(456) // Should be cast to int
        ->willReturn(new Collection([]));

    ob_start();
    try {
        $this->exporter->export([
            'achievementId' => $achievementId,
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
        'achievementId' => 789,
        'other'         => 'param',
    ];

    $this->repository
        ->method('getByAchievementIdWithEditor')
        ->willReturn(new Collection([]));

    $this->logger
        ->expects($this->once())
        ->method('info')
        ->with('Achievement unlocked', [
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
