<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

use App\Common\Attribute\RpcParam;

readonly class CreateKpiMetricDepartmentRequest
{
    public function __construct(
        #[RpcParam('id департамента')] public int $departmentId,
        #[RpcParam('id должности')] public int $postId,
    ) {
    }
}
