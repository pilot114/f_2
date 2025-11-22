<?php

declare(strict_types=1);

use Sentry\SentryBundle\SentryBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

return [
    FrameworkBundle::class => [
        'all' => true,
    ],
    SecurityBundle::class => [
        'all' => true,
    ],
    MonologBundle::class => [
        'all' => true,
    ],
    SentryBundle::class => [
        'prod' => true,
    ],
];
