<?php

declare(strict_types=1);

// Загружаем preload только для prod окружения
$env = null;
if (file_exists(__DIR__ . '/../.env.local.php')) {
    $envs = include __DIR__ . '/../.env.local.php';
    $env = $envs['APP_ENV'];
}

if ($env === 'prod' && file_exists(dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';
}
