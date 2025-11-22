<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Dit\ServiceDesk\DTO\IssuesStatsResponse;
use App\Domain\Dit\ServiceDesk\UseCase\GetIssuesStatsUseCase;

class GetIssuesStatsController
{
    public function __construct(
        private GetIssuesStatsUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'dit.serviceDesk.getIssuesStats',
        'Получить статистику по задачам за последние 3 месяца (созданные и выполненные)',
    )]
    public function __invoke(): IssuesStatsResponse
    {
        $stats = $this->useCase->getStats();

        return IssuesStatsResponse::build($stats);
    }
}
