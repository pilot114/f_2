<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Dit\Reporter\UseCase\GetReportListUseCase;

readonly class GetReportListController
{
    public function __construct(
        private GetReportListUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'dit.reporter.getReportList',
        'список отчётов',
    )]
    /**
     * @return int[]
     */
    public function __invoke(): array
    {
        return $this->useCase->getReportList();
    }
}
