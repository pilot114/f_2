<?php

declare(strict_types=1);

use App\System\Exception\ConfigurationException;
use App\System\Factory\JiraConfigurationFactory;
use JiraRestApi\Configuration\ArrayConfiguration;

it('creates configuration with valid parameters', function (): void {
    $factory = new JiraConfigurationFactory(
        'https://jira.example.com',
        'test_user',
        'test_password'
    );

    $config = $factory->create();

    expect($config)->toBeInstanceOf(ArrayConfiguration::class);
});

it('creates configuration with use v3 rest api enabled', function (): void {
    $factory = new JiraConfigurationFactory(
        'https://jira.example.com',
        'test_user',
        'test_password',
        true
    );

    $config = $factory->create();

    expect($config)->toBeInstanceOf(ArrayConfiguration::class);
});

it('throws exception when jira host is empty', function (): void {
    expect(fn (): JiraConfigurationFactory => new JiraConfigurationFactory(
        '',
        'test_user',
        'test_password'
    ))->toThrow(ConfigurationException::class, 'Не указаны параметры конфигурации Jira: jiraHost');
});

it('throws exception when jira user is empty', function (): void {
    expect(fn (): JiraConfigurationFactory => new JiraConfigurationFactory(
        'https://jira.example.com',
        '',
        'test_password'
    ))->toThrow(ConfigurationException::class, 'Не указаны параметры конфигурации Jira: jiraUser');
});

it('throws exception when jira password is empty', function (): void {
    expect(fn (): JiraConfigurationFactory => new JiraConfigurationFactory(
        'https://jira.example.com',
        'test_user',
        ''
    ))->toThrow(ConfigurationException::class, 'Не указаны параметры конфигурации Jira: jiraPassword');
});

it('throws exception when multiple parameters are empty', function (): void {
    expect(fn (): JiraConfigurationFactory => new JiraConfigurationFactory(
        '',
        '',
        'test_password'
    ))->toThrow(ConfigurationException::class, 'Не указаны параметры конфигурации Jira: jiraHost, jiraUser');
});

it('throws exception when all parameters are empty', function (): void {
    expect(fn (): JiraConfigurationFactory => new JiraConfigurationFactory(
        '',
        '',
        ''
    ))->toThrow(ConfigurationException::class, 'Не указаны параметры конфигурации Jira: jiraHost, jiraUser, jiraPassword');
});
