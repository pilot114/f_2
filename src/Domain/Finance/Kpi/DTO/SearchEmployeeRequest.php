<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;

readonly class SearchEmployeeRequest
{
    public function __construct(
        #[RpcParam('ФИО сотрудника для поиска')]
        public string $search,
        #[RpcParam('ID пользователя (опционально для замещения)', required: false)]
        public ?int $userId = null,
    ) {
    }
}
