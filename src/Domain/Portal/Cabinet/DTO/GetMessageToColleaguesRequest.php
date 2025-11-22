<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Common\Attribute\RpcParam;

readonly class GetMessageToColleaguesRequest
{
    public function __construct(
        #[RpcParam('ID пользователя для получения сообщения')]
        public int $userId,
    ) {
    }
}
