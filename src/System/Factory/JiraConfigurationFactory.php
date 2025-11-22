<?php

declare(strict_types=1);

namespace App\System\Factory;

use App\System\Exception\ConfigurationException;
use JiraRestApi\Configuration\ArrayConfiguration;

class JiraConfigurationFactory
{
    public function __construct(
        private string $jiraHost,
        private string $jiraUser,
        private string $jiraPassword,
        private bool $useV3RestApi = false,
    ) {
        $missingParams = [];
        if ($this->jiraHost === '') {
            $missingParams[] = 'jiraHost';
        }
        if ($this->jiraUser === '') {
            $missingParams[] = 'jiraUser';
        }
        if ($this->jiraPassword === '') {
            $missingParams[] = 'jiraPassword';
        }
        if ($missingParams !== []) {
            throw new ConfigurationException(
                'Не указаны параметры конфигурации Jira: ' . implode(', ', $missingParams)
            );
        }
    }

    public function create(): ArrayConfiguration
    {
        return new ArrayConfiguration([
            'jiraHost'     => $this->jiraHost,
            'jiraUser'     => $this->jiraUser,
            'jiraPassword' => $this->jiraPassword,
            'useV3RestApi' => $this->useV3RestApi,
        ]);
    }
}
