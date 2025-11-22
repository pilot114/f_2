<?php

declare(strict_types=1);

use App\System\Factory\DbLoggerFactory;
use Database\Connection\Logger\LogType;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

beforeEach(function (): void {
    $this->cache = new ArrayAdapter();
    $this->user = createSecurityUser(123, 'test_user');
});

it('builds logger for non-cache type without profiler cookie', function (): void {
    $factory = new DbLoggerFactory($this->user);
    $logger = $factory->build(LogType::FILE, $this->cache);

    expect($logger)->toBeInstanceOf(LoggerInterface::class);
    expect($logger)->not->toBeInstanceOf(NullLogger::class);
});

it('returns null logger for cache type without profiler cookie', function (): void {
    $factory = new DbLoggerFactory($this->user);
    $logger = $factory->build(LogType::CACHE, $this->cache);

    expect($logger)->toBeInstanceOf(NullLogger::class);
});

it('builds logger for cache type with profiler cookie', function (): void {
    $_COOKIE['sql_profiler_enabled'] = '1';

    $factory = new DbLoggerFactory($this->user);
    $logger = $factory->build(LogType::CACHE, $this->cache);

    expect($logger)->toBeInstanceOf(LoggerInterface::class);
    expect($logger)->not->toBeInstanceOf(NullLogger::class);

    unset($_COOKIE['sql_profiler_enabled']);
});

it('works with null user', function (): void {
    $factory = new DbLoggerFactory();
    $logger = $factory->build(LogType::FILE, $this->cache);

    expect($logger)->toBeInstanceOf(LoggerInterface::class);
});

it('uses custom file path', function (): void {
    $factory = new DbLoggerFactory($this->user);
    $logger = $factory->build(LogType::FILE, $this->cache, './custom.log');

    expect($logger)->toBeInstanceOf(LoggerInterface::class);
});
