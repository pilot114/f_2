<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Common\Attribute\RpcParam;

readonly class GetUsersListRequest
{
    public function __construct(
        #[RpcParam('Поиск по имени пользователя')]
        public string $search,
    ) {
    }
}
