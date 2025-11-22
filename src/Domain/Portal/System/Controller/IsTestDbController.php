<?php

declare(strict_types=1);

namespace App\Domain\Portal\System\Controller;

use App\Common\Attribute\RpcMethod;

class IsTestDbController
{
    public function __construct(
        private bool $isProd
    ) {
    }

    #[RpcMethod(
        'portal.system.isTestDb',
        'Проверка что выбрана тестовая БД',
    )]
    public function __invoke(
    ): bool {
        return !$this->isProd;
    }
}
