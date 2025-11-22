<?php

declare(strict_types=1);

namespace App\Domain\Portal\System\Controller;

use App\Common\Attribute\RpcMethod;

class EmptyController
{
    #[RpcMethod(
        name: 'portal.system.empty',
        summary: 'Минимальный пример эндпоинта для проверки быстродействия',
    )]
    public function __invoke(): int
    {
        return 42;
    }

}
