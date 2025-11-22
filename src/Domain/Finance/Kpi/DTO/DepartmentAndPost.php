<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\DTO;

class DepartmentAndPost
{
    public function __construct(
        public int $departmentId,
        public string $departmentName,
        public int $postId,
        public string $postName,
    ) {
    }
}
