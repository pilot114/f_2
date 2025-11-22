<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\UseCase;

use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;
use App\Domain\Dit\ServiceDesk\Service\ServiceDeskStatsService;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class GetRatingsStatsUseCase
{
    public const CACHE_TTL = 86400;
    public const CACHE_KEY = 'servicedesk_ratings_stats_';

    public function __construct(
        private ServiceDeskStatsService $serviceDeskStatsService,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function getStats(int $year): YearlyRatingsStats
    {
        $cacheKey = self::CACHE_KEY . $year;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            /** @var array{year: int, averageRating: float|null, ratingsCount: int|null} $cachedData */
            $cachedData = $cacheItem->get();

            return new YearlyRatingsStats(
                year: $cachedData['year'],
                averageRating: $cachedData['averageRating'],
                ratingsCount: $cachedData['ratingsCount'],
            );
        }

        $stats = $this->fetchRatingsStats($year);
        $this->cacheStats($stats, $cacheItem);
        return $stats;
    }

    private function fetchRatingsStats(int $year): YearlyRatingsStats
    {
        return $this->serviceDeskStatsService->getRatingsStatsForYear($year);
    }

    private function cacheStats(YearlyRatingsStats $stats, CacheItemInterface $cacheItem): void
    {
        $data = [
            'year'          => $stats->year,
            'averageRating' => $stats->averageRating,
            'ratingsCount'  => $stats->ratingsCount,
        ];

        $cacheItem->set($data);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);
    }
}
