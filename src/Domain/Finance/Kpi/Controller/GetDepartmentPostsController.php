<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\DTO\DepartmentAndPost;
use App\Domain\Finance\Kpi\UseCase\GetDepartmentPostsUseCase;

class GetDepartmentPostsController
{
    public function __construct(
        private GetDepartmentPostsUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<DepartmentAndPost>
     */
    #[RpcMethod(
        'finance.kpi.getDepartmentPosts',
        'получение списка должностей+департаментов, заданных для KPI',
    )]
    #[CpAction('accured_kpi.accured_kpi_admin')]
    public function __invoke(): FindResponse
    {
        $items = $this->useCase->getDepartmentPosts();
        return new FindResponse($items);
    }
}
