<?php

declare(strict_types=1);

namespace App\System\Factory;

use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\Logger\LogType;
use Database\Factory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Psr16Cache;

readonly class DbLoggerFactory
{
    public function __construct(
        private ?SecurityUser $user = null,
    ) {
    }

    public function build(LogType $type, CacheItemPoolInterface $cache, string $filePath = './sql.log'): LoggerInterface
    {
        $isProfilerEnabled = isset($_COOKIE['sql_profiler_enabled']);

        if ($isProfilerEnabled === false && $type === LogType::CACHE) {
            return new NullLogger();
        }
        $psrCache = new Psr16Cache($cache);
        $cacheKey = 'sql_profiler-' . $this->user?->id;

        return Factory::buildDefaultLogger($type, $filePath, $psrCache, $cacheKey);
    }
}
