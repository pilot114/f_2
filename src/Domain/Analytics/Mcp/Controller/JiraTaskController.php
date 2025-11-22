<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Controller;

use App\Domain\Analytics\Mcp\UseCase\JiraTaskUseCase;
use PhpMcp\Server\Attributes\{McpPrompt, McpTool, Schema};

class JiraTaskController
{
    public function __construct(
        private JiraTaskUseCase $useCase,
    ) {
    }

    #[McpPrompt(
        name: 'add-feature',
        description: 'Промпт для планирования выполнения задачи разработки'
    )]
    public function addFeature(
        #[Schema(description: 'code задачи в Jira')]
        string $code
    ): array {
        return $this->useCase->planningTask($code);
    }

    #[McpTool(
        name: 'jira-tasks',
        description: 'Получить список задач JQL по фильтру. Пример значения для получения одной задачи: issuekey = "KPI-140"'
    )]
    public function jiraTask(
        #[Schema(description: 'JQL фильтр')]
        string $jql = ''
    ): string {
        return $this->useCase->getTasksByJql($jql);
    }

    #[McpTool(
        name: 'jira-tasks-planned',
        description: 'Получить планируемые задачи за заданный период',
    )]
    public function plannedTask(
        #[Schema(description: 'start period', format: 'date')]
        string $from,
        #[Schema(description: 'end period', format: 'date')]
        string $to
    ): string {
        return $this->useCase->getPlannedTasks($from, $to);
    }
}
