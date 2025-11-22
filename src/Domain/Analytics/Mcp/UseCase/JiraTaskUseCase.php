<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\UseCase;

use App\Common\Service\Integration\ConfluenceClient;
use App\Common\Service\Integration\JiraClient;
use App\Common\Service\Integration\TempoJiraClient;
use JiraRestApi\JiraException;

class JiraTaskUseCase
{
    public function __construct(
        private JiraClient $jira,
        private TempoJiraClient $tempoJira,
        private ConfluenceClient $confluenceClient,
    ) {
    }

    public function planningTask(string $code): array
    {
        $template = file_get_contents(__DIR__ . '/../../../../../docs/howto/design_a_new_service_llm.md');

        if ($template === false) {
            return [];
        }

        return [
            [
                'role'    => 'user',
                'content' => $this->replaceTokens($template, [
                    'JIRA_TASK_CODE' => strtoupper($code),
                ]),
            ],
        ];
    }

    private function replaceTokens(string $template, array $vars): string
    {
        $result = preg_replace_callback('/\{\{(\w+)}}/', fn ($matches) => $vars[$matches[1]] ?? $matches[0], $template);
        return $result ?? $template;
    }

    public function getTasksByJql(string $jql): string
    {
        $jql = $jql !== '' ? $jql : 'created >= startOfDay() AND created <= endOfDay()';

        try {
            $text = json_encode($this->jira->getAllIssuesByJql($jql));
        } catch (JiraException $e) {
            $text = $e->getResponse();
        }
        $data = json_decode((string) $text, true);

        if (!is_array($data)) {
            return (string) $text;
        }

        // запрашиваем конкретную задачу
        if (str_contains($jql, 'issuekey')) {
            foreach ($data as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!isset($item['key'])) {
                    continue;
                }
                if (!is_string($item['key'])) {
                    continue;
                }
                if (str_contains($jql, $item['key'])) {
                    $info = 'Summary: ' . ($item['fields']['summary'] ?? '') . "\n";
                    $info .= 'Description: ' . ($item['fields']['description'] ?? '') . "\n\n";

                    // ищем ссылки на Confluence и подставляем содержимое страниц
                    foreach ($this->extractLinks($info) as $name => $link) {
                        if (str_starts_with($link, 'https://docs.siberianhealth.com')) {
                            $pageId = $this->getConfluencePageId($link);
                            if ($pageId !== null) {
                                $content = $this->confluenceClient->getContent($pageId);
                                $info .= "$name: " . $content . "\n";
                            }
                        }
                    }
                    return $info;
                }
            }
            return 'Not found';
        }

        $result = json_encode($data);
        return $result !== false ? $result : '';
    }

    public function getPlannedTasks(string $from, string $to): string
    {
        try {
            $text = json_encode([
                'users' => $this->tempoJira->getPlannedUsers(),
                'plan'  => $this->tempoJira->getPlanned(),
            ]);
        } catch (JiraException $e) {
            $text = $e->getResponse();
        }
        return (string) $text;
    }

    private function getConfluencePageId(string $link): ?int
    {
        if (str_starts_with($link, 'https://docs.siberianhealth.com')) {
            $parts = explode('/', $link);
            foreach ($parts as $i => $part) {
                if ($part === 'pages') {
                    return (int) $parts[$i + 1]; // ID страницы
                }
            }
        }
        return null;
    }

    private function extractLinks(string $text): array
    {
        $pattern = '/\[([^|\]]+)\|([^\]]+)\]/u';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        $links = [];
        foreach ($matches as $match) {
            $links[$match[1]] = $match[2];
        }
        return $links;
    }
}
