<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\UseCase;

use App\Domain\Dit\ServiceDesk\Entity\MonthlyIssuesStats;
use App\Domain\Dit\ServiceDesk\Service\ServiceDeskStatsService;
use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class GetIssuesStatsUseCase
{
    public const CACHE_TTL = 86400;
    public const CACHE_KEY = 'servicedesk_issues_stats_';

    public function __construct(
        private ServiceDeskStatsService $serviceDeskStatsService,
        private CacheItemPoolInterface $cache,
    ) {
    }

    /** @return MonthlyIssuesStats[]*/
    public function getStats(): array
    {
        $currentMonth = (new DateTimeImmutable())->modify('first day of this month');
        $monthlyStats = [];

        for ($i = 0; $i < 3; $i++) {
            $month = $currentMonth->modify("-$i months");
            $stats = $this->getMonthlyStats($month);
            $monthlyStats[] = $stats;
        }

        usort($monthlyStats, fn ($a, $b): int => $a->month <=> $b->month);

        return $monthlyStats;
    }

    public function getMonthlyStats(DateTimeImmutable $month): MonthlyIssuesStats
    {
        $year = $month->format('Y');
        $monthNum = $month->format('m');
        $cacheKey = self::CACHE_KEY . $year . '_' . $monthNum;

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            /** @var array{month: DateTimeImmutable, createdIssues: int, resolvedIssues: int} $cachedData */
            $cachedData = $cacheItem->get();

            return new MonthlyIssuesStats(
                month: $cachedData['month'],
                createdIssues: $cachedData['createdIssues'],
                resolvedIssues: $cachedData['resolvedIssues'],
            );
        }

        $stats = $this->fetchMonthlyStats($month);
        $this->cacheMonthlyStats($stats, $cacheItem);
        return $stats;
    }

    private function fetchMonthlyStats(DateTimeImmutable $month): MonthlyIssuesStats
    {
        $createdIssues = $this->serviceDeskStatsService->getCreatedIssuesCountByMonth($month);
        $resolvedIssues = $this->serviceDeskStatsService->getResolvedIssuesCountByMonth($month);

        return new MonthlyIssuesStats(
            month: $month,
            createdIssues: $createdIssues,
            resolvedIssues: $resolvedIssues,
        );
    }

    private function cacheMonthlyStats(MonthlyIssuesStats $stats, CacheItemInterface $cacheItem): void
    {
        $data = [
            'month'          => $stats->month,
            'createdIssues'  => $stats->createdIssues,
            'resolvedIssues' => $stats->resolvedIssues,
        ];

        $cacheItem->set($data);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);
    }
}
