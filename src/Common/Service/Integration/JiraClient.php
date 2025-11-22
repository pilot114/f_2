<?php

declare(strict_types=1);

namespace App\Common\Service\Integration;

use JiraRestApi\Issue\IssueService;
use JsonMapper_Exception;

class JiraClient
{
    public function __construct(
        private IssueService $issueService
    ) {
    }

    /**
     * Получить список Issues по условиям
     */
    public function getAllIssuesByJql(string $jql, array $fields = [], array $expand = [], int $start = 0, int $limit = 100): array
    {
        $issues = [];

        try {
            $total = $this->issueService->search($jql)->getTotal();
        } catch (JsonMapper_Exception $exception) {
            if (strpos($exception->getMessage(), "property \"expand\"")) {
                return $issues;
            }
            throw $exception;
        }

        while (count($issues) < $total) {
            $tasks = $this->issueService->search($jql, $start, $limit, $fields, $expand);
            $batchIssues = $tasks->getIssues();
            $issues = array_merge($issues, $batchIssues);
            $start += $limit;
        }
        return $issues;
    }

    /**
     * Получить количество Issues по условиям
     */
    public function getIssuesCount(string $jql): int
    {
        try {
            $result = $this->issueService->search($jql, 0, 1);
            return $result->getTotal();
        } catch (JsonMapper_Exception $exception) {
            if (strpos($exception->getMessage(), "property \"expand\"")) {
                return 0;
            }
            throw $exception;
        }
    }
}
