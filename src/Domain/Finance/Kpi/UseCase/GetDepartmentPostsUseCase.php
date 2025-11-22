<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\DepartmentAndPost;
use App\Domain\Finance\Kpi\Repository\PostQueryRepository;

class GetDepartmentPostsUseCase
{
    public function __construct(
        private PostQueryRepository $repo,
    ) {
    }

    /**
     * @return DepartmentAndPost[]
     */
    public function getDepartmentPosts(): array
    {
        return array_map(
            static fn (array $data): DepartmentAndPost => new DepartmentAndPost(...$data),
            $this->repo->getPostsWithDepartments(),
        );
    }
}
