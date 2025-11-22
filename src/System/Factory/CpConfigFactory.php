<?php

declare(strict_types=1);

namespace App\System\Factory;

use Database\Connection\CpConfig;

class CpConfigFactory
{
    public function get(
        string $user,
        string $password,
        bool $isProd,
    ): CpConfig {
        if (isset($_COOKIE['db_test'])) {
            $isProd = $_COOKIE['db_test'] !== 'true';
        }
        return new CpConfig(
            user: $user,
            password: $password,
            isProd: $isProd
        );
    }
}
