<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\ServiceDesk\UseCase;

use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;
use App\Domain\Dit\ServiceDesk\Service\ServiceDeskStatsService;
use App\Domain\Dit\ServiceDesk\UseCase\GetRatingsStatsUseCase;
use Mockery;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

it('returns stats for given year when no cache', function (): void {
    $year = 2024;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem->shouldReceive('set')->once();
    $cacheItem->shouldReceive('expiresAfter')->once()->with(GetRatingsStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    $cache->shouldReceive('save')
        ->with($cacheItem)
        ->once();

    $expectedStats = new YearlyRatingsStats(
        year: $year,
        averageRating: 4.2,
        ratingsCount: 150
    );

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getRatingsStatsForYear')
        ->with($year)
        ->once()
        ->andReturn($expectedStats);

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBe(4.2);
    expect($result->ratingsCount)->toBe(150);

    Mockery::close();
});

it('returns cached data when available', function (): void {
    $year = 2023;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cachedData = [
        'year'          => $year,
        'averageRating' => 3.8,
        'ratingsCount'  => 95,
    ];

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(true);
    $cacheItem->shouldReceive('get')->once()->andReturn($cachedData);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    // Service should not be called when data is cached
    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService->shouldNotReceive('getRatingsStatsForYear');

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBe(3.8);
    expect($result->ratingsCount)->toBe(95);

    Mockery::close();
});

it('handles stats with null values when no cache', function (): void {
    $year = 2022;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem->shouldReceive('set')->once();
    $cacheItem->shouldReceive('expiresAfter')->once()->with(GetRatingsStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    $cache->shouldReceive('save')
        ->with($cacheItem)
        ->once();

    $expectedStats = new YearlyRatingsStats(
        year: $year,
        averageRating: null,
        ratingsCount: null
    );

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getRatingsStatsForYear')
        ->with($year)
        ->once()
        ->andReturn($expectedStats);

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBeNull();
    expect($result->ratingsCount)->toBeNull();

    Mockery::close();
});

it('handles cached stats with null values', function (): void {
    $year = 2021;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cachedData = [
        'year'          => $year,
        'averageRating' => null,
        'ratingsCount'  => null,
    ];

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(true);
    $cacheItem->shouldReceive('get')->once()->andReturn($cachedData);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService->shouldNotReceive('getRatingsStatsForYear');

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats($year);

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);
    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBeNull();
    expect($result->ratingsCount)->toBeNull();

    Mockery::close();
});

it('handles zero values correctly', function (): void {
    $year = 2020;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem->shouldReceive('set')->once();
    $cacheItem->shouldReceive('expiresAfter')->once()->with(GetRatingsStatsUseCase::CACHE_TTL);

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    $cache->shouldReceive('save')
        ->with($cacheItem)
        ->once();

    $expectedStats = new YearlyRatingsStats(
        year: $year,
        averageRating: 0.0,
        ratingsCount: 0
    );

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getRatingsStatsForYear')
        ->with($year)
        ->once()
        ->andReturn($expectedStats);

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats($year);

    expect($result->year)->toBe($year);
    expect($result->averageRating)->toBe(0.0);
    expect($result->ratingsCount)->toBe(0);

    Mockery::close();
});

it('uses correct cache key format', function (): void {
    $year = 2024;
    $cacheKey = GetRatingsStatsUseCase::CACHE_KEY . $year;

    $cacheItem = Mockery::mock(CacheItemInterface::class);
    $cacheItem->shouldReceive('isHit')->once()->andReturn(false);
    $cacheItem->shouldReceive('set')->once();
    $cacheItem->shouldReceive('expiresAfter')->once();

    $cache = Mockery::mock(CacheItemPoolInterface::class);
    $cache->shouldReceive('getItem')
        ->with($cacheKey)
        ->once()
        ->andReturn($cacheItem);

    $cache->shouldReceive('save')->once();

    $stats = new YearlyRatingsStats(
        year: 2024,
        averageRating: 4.5,
        ratingsCount: 100
    );

    $serviceDeskStatsService = Mockery::mock(ServiceDeskStatsService::class);
    $serviceDeskStatsService
        ->shouldReceive('getRatingsStatsForYear')
        ->with(2024)
        ->once()
        ->andReturn($stats);

    $useCase = new GetRatingsStatsUseCase($serviceDeskStatsService, $cache);
    $result = $useCase->getStats(2024);
    $cache->shouldHaveReceived('getItem');

    expect($result)->toBeInstanceOf(YearlyRatingsStats::class);

    Mockery::close();
});
