<?php

declare(strict_types=1);

namespace App\Domain\Portal\System\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Psr\Cache\CacheItemPoolInterface;

class SqlProfilerController
{
    public function __construct(
        private SecurityUser $user,
        private CacheItemPoolInterface $cache
    ) {
    }

    #[RpcMethod(
        'portal.system.getSqlProfilerData',
        'получить данные sql-профайлера',
    )]

    /**  @return int[] */
    public function get(
    ): array {
        $cache = $this->cache->getItem('sql_profiler-' . $this->user->id);
        return (array) ($cache->get() ?? []);
    }

    #[RpcMethod(
        'portal.system.deleteSqlProfilerData',
        'очистить данные sql-профайлера',
    )]
    public function delete(
    ): void {
        $this->cache->deleteItem('sql_profiler-' . $this->user->id);
    }
}
