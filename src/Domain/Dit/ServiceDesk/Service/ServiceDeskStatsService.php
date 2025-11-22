<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\Service;

use App\Common\Service\Integration\JiraClient;
use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;
use DateTimeImmutable;

class ServiceDeskStatsService
{
    private const PROJECT = '10002';
    private const FEEDBACK_FIELD = 'cf[10027]';

    public function __construct(
        private JiraClient $jiraClient
    ) {
    }

    /**
     * Получить количество созданных тикетов за месяц
     */
    public function getCreatedIssuesCountByMonth(DateTimeImmutable $month): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($month);
        $monthsDiff = ($diff->invert ? -1 : 1) * ($diff->y * 12 + $diff->m);

        $jql = sprintf(
            'project = "%s"
             AND created >= startOfMonth(%d) AND created <= endOfMonth(%d)
             AND issuetype in (Evaluating, "Service request", Incident, Problem)',
            self::PROJECT,
            $monthsDiff,
            $monthsDiff
        );

        return $this->jiraClient->getIssuesCount($jql);
    }

    /**
     * Получить количество выполненных тикетов за месяц
     */
    public function getResolvedIssuesCountByMonth(DateTimeImmutable $month): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($month);
        $monthsDiff = ($diff->invert ? -1 : 1) * ($diff->y * 12 + $diff->m);

        $jql = sprintf(
            'project = "%s" 
            AND resolved >= startOfMonth(%d) AND resolved <= endOfMonth(%d)
            AND issuetype in (Evaluating, "Service request", Incident, Problem)',
            self::PROJECT,
            $monthsDiff,
            $monthsDiff
        );

        return $this->jiraClient->getIssuesCount($jql);
    }

    /**
     * Получить статистику по оценкам за год
     */
    public function getRatingsStatsForYear(int $year): YearlyRatingsStats
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        $ratingCounts = [];

        for ($i = 1; $i <= 5; $i++) {
            $jql = 'project = "' . self::PROJECT . '" 
            AND resolutiondate >= "' . $startDate . '"
            AND resolutiondate <= "' . $endDate . '"
            AND ' . self::FEEDBACK_FIELD . '     =' . $i
            . ' AND issuetype in (Evaluating, "Service request", Incident, Problem)';

            $count = $this->jiraClient->getIssuesCount($jql);
            if ($count > 0) {
                $ratingCounts[$i] = $count;
            }
        }

        return $this->resolveYearlyStats($ratingCounts, $year);
    }

    private function resolveYearlyStats(array $ratingCounts, int $year): YearlyRatingsStats
    {
        $totalScore = 0;
        $totalCount = 0;

        foreach ($ratingCounts as $rating => $count) {
            $totalScore += $rating * $count;
            $totalCount += $count;
        }

        $averageRating = $totalCount > 0 ? $totalScore / $totalCount : 0.0;

        return new YearlyRatingsStats(
            year: $year,
            averageRating: $averageRating > 0 ? round($averageRating, 2) : null,
            ratingsCount: $totalCount > 0 ? (int) $totalCount : null,
        );
    }
}
