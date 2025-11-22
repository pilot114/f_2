<?php

declare(strict_types=1);

use App\System\Factory\CpConfigFactory;
use Database\Connection\CpConfig;

beforeEach(function (): void {
    $this->factory = new CpConfigFactory();
    // Clean cookies before each test
    if (isset($_COOKIE['db_test'])) {
        unset($_COOKIE['db_test']);
    }
});

afterEach(function (): void {
    // Clean cookies after each test
    if (isset($_COOKIE['db_test'])) {
        unset($_COOKIE['db_test']);
    }
});

it('creates config with production true', function (): void {
    $config = $this->factory->get('test_user', 'test_pass', true);

    expect($config)->toBeInstanceOf(CpConfig::class);
    expect($config->user)->toBe('test_user');
    expect($config->password)->toBe('test_pass');
    expect($config->isProd)->toBe(true);
});

it('creates config with production false', function (): void {
    $config = $this->factory->get('test_user', 'test_pass', false);

    expect($config)->toBeInstanceOf(CpConfig::class);
    expect($config->user)->toBe('test_user');
    expect($config->password)->toBe('test_pass');
    expect($config->isProd)->toBe(false);
});

it('overrides production setting when db_test cookie is true', function (): void {
    $_COOKIE['db_test'] = 'true';

    $config = $this->factory->get('test_user', 'test_pass', true);

    expect($config->isProd)->toBe(false);
});

it('overrides production setting when db_test cookie is false', function (): void {
    $_COOKIE['db_test'] = 'false';

    $config = $this->factory->get('test_user', 'test_pass', false);

    expect($config->isProd)->toBe(true);
});

it('handles non-boolean db_test cookie values', function (): void {
    $_COOKIE['db_test'] = 'some_value';

    $config = $this->factory->get('test_user', 'test_pass', false);

    expect($config->isProd)->toBe(true);
});
