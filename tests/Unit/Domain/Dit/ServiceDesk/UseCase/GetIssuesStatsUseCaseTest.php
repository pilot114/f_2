<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\ServiceDesk\UseCase;

use App\Domain\Dit\ServiceDesk\Entity\MonthlyIssuesStats;
use App\Domain\Dit\ServiceDesk\Service\ServiceDeskStatsService;
use App\Domain\Dit\ServiceDesk\UseCase\GetIssuesStatsUseCase;
use DateTimeImmutable;
use Mockery;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

it('returns stats for last 3 months when no cache', function (): void {
    // Create mock cache items for 3 months
    $cacheItem1 = Mockery::mock(CacheItemInterface::class);
    $cacheItem1->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem1->shouldReceive('set')->once();
    $cacheItem1->shouldReceive('expiresAfter')->once()->with(GetIssuesStatsUseCase::CACHE_TTL);

    $cacheItem2 = Mockery::mock(CacheItemInterface::class);
    $cacheItem2->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem2->shouldReceive('set')->once();
    $cacheItem2->shouldReceive('expiresAfter')->once()->with(GetIssuesStatsUseCase::CACHE_TTL);

    $cacheItem3 = Mockery::mock(CacheItemInterface::class);
    $cacheItem3->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem3->shouldReceive('set')->once();
    $cacheItem3->shouldReceive('expiresAfter')->once()->with(GetIssuesStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->times(3)
        ->andReturn($cacheItem1, $cacheItem2, $cacheItem3);

    $cache->shouldReceive('save')->times(3);

    // Mock service calls
    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getCreatedIssuesCountByMonth')
        ->times(3)
        ->andReturn(30, 20, 15);

    $serviceDeskStatsService
        ->shouldReceive('getResolvedIssuesCountByMonth')
        ->times(3)
        ->andReturn(25, 18, 12);

    $useCase = new GetIssuesStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats();

    expect($result)->toHaveCount(3);
    expect($result)->each->toBeInstanceOf(MonthlyIssuesStats::class);

    // Results should be sorted by month ascending
    expect($result[0]->createdIssues)->toBe(15);
    expect($result[0]->resolvedIssues)->toBe(12);

    expect($result[1]->createdIssues)->toBe(20);
    expect($result[1]->resolvedIssues)->toBe(18);

    expect($result[2]->createdIssues)->toBe(30);
    expect($result[2]->resolvedIssues)->toBe(25);

    Mockery::close();
});

it('returns cached data when available', function (): void {
    $cachedData1 = [
        'month'          => new DateTimeImmutable('2024-01-01'),
        'createdIssues'  => 10,
        'resolvedIssues' => 8,
    ];

    $cachedData2 = [
        'month'          => new DateTimeImmutable('2024-02-01'),
        'createdIssues'  => 15,
        'resolvedIssues' => 12,
    ];

    $cachedData3 = [
        'month'          => new DateTimeImmutable('2024-03-01'),
        'createdIssues'  => 20,
        'resolvedIssues' => 18,
    ];

    $cacheItem1 = Mockery::mock(CacheItemInterface::class);
    $cacheItem1->shouldReceive('isHit')->once()->andReturn(true);
    $cacheItem1->shouldReceive('get')->once()->andReturn($cachedData3);

    $cacheItem2 = Mockery::mock(CacheItemInterface::class);
    $cacheItem2->shouldReceive('isHit')->once()->andReturn(true);
    $cacheItem2->shouldReceive('get')->once()->andReturn($cachedData2);

    $cacheItem3 = Mockery::mock(CacheItemInterface::class);
    $cacheItem3->shouldReceive('isHit')->once()->andReturn(true);
    $cacheItem3->shouldReceive('get')->once()->andReturn($cachedData1);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->times(3)
        ->andReturn($cacheItem1, $cacheItem2, $cacheItem3);

    // Service should not be called when data is cached
    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService->shouldNotReceive('getCreatedIssuesCountByMonth');
    $serviceDeskStatsService->shouldNotReceive('getResolvedIssuesCountByMonth');

    $useCase = new GetIssuesStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats();

    expect($result)->toHaveCount(3);

    // Results should be sorted by month ascending
    expect($result[0]->month->format('Y-m-d'))->toBe('2024-01-01');
    expect($result[0]->createdIssues)->toBe(10);
    expect($result[0]->resolvedIssues)->toBe(8);

    expect($result[1]->month->format('Y-m-d'))->toBe('2024-02-01');
    expect($result[1]->createdIssues)->toBe(15);
    expect($result[1]->resolvedIssues)->toBe(12);

    expect($result[2]->month->format('Y-m-d'))->toBe('2024-03-01');
    expect($result[2]->createdIssues)->toBe(20);
    expect($result[2]->resolvedIssues)->toBe(18);

    Mockery::close();
});

it('handles zero values correctly', function (): void {
    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->times(3)->andReturn(false);
    $cacheItem->shouldReceive('set')->times(3);
    $cacheItem->shouldReceive('expiresAfter')->times(3)->with(GetIssuesStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')->times(3)->andReturn($cacheItem);
    $cache->shouldReceive('save')->times(3);

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getCreatedIssuesCountByMonth')
        ->times(3)
        ->andReturn(0);

    $serviceDeskStatsService
        ->shouldReceive('getResolvedIssuesCountByMonth')
        ->times(3)
        ->andReturn(0);

    $useCase = new GetIssuesStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats();

    expect($result)->toHaveCount(3);
    expect($result[0]->createdIssues)->toBe(0);
    expect($result[0]->resolvedIssues)->toBe(0);

    Mockery::close();
});

it('uses correct cache keys', function (): void {
    $service = Mockery::mock(ServiceDeskStatsService::class);
    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cacheItem = Mockery::mock(CacheItemInterface::class);

    $date = (new DateTimeImmutable())->modify('first day of this month');
    $cacheKey1 = 'servicedesk_issues_stats_' . $date->format('Y_m');
    $cacheKey2 = 'servicedesk_issues_stats_' . $date->modify('-1 month')->format('Y_m');
    $cacheKey3 = 'servicedesk_issues_stats_' . $date->modify('-2 months')->format('Y_m');

    $cache->shouldReceive('getItem')
        ->with(Mockery::anyOf($cacheKey1, $cacheKey2, $cacheKey3))
        ->times(3)
        ->andReturn($cacheItem);

    $cacheItem->shouldReceive('isHit')->times(3)->andReturn(false);
    $service->shouldReceive('getCreatedIssuesCountByMonth')->times(3)->andReturn(10);
    $service->shouldReceive('getResolvedIssuesCountByMonth')->times(3)->andReturn(8);

    $cacheItem->shouldReceive('set')->times(3);
    $cacheItem->shouldReceive('expiresAfter')->times(3)->with(86400);
    $cache->shouldReceive('save')->times(3)->with($cacheItem);

    $useCase = new GetIssuesStatsUseCase($service, $cache);

    $result = $useCase->getStats();

    expect($result)->toHaveCount(3);
    expect($result[0]->createdIssues)->toBe(10);
    expect($result[0]->resolvedIssues)->toBe(8);

    $cache->shouldHaveReceived('getItem');

    Mockery::close();
});

it('getMonthlyStats returns correct data for specific month', function (): void {
    $month = new DateTimeImmutable('2024-03-01');
    $cacheKey = 'servicedesk_issues_stats_2024_03';

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem->shouldReceive('set')->once();
    $cacheItem->shouldReceive('expiresAfter')->once()->with(GetIssuesStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);
    $cache->shouldReceive('save')->once();

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getCreatedIssuesCountByMonth')
        ->with($month)
        ->once()
        ->andReturn(25);

    $serviceDeskStatsService
        ->shouldReceive('getResolvedIssuesCountByMonth')
        ->with($month)
        ->once()
        ->andReturn(22);

    $useCase = new GetIssuesStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getMonthlyStats($month);

    expect($result)->toBeInstanceOf(MonthlyIssuesStats::class);
    expect($result->month->format('Y-m-d'))->toBe('2024-03-01');
    expect($result->createdIssues)->toBe(25);
    expect($result->resolvedIssues)->toBe(22);

    Mockery::close();
});
