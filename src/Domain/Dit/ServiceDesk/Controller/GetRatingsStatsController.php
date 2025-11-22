<?php

declare(strict_types=1);

namespace App\Domain\Dit\ServiceDesk\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Dit\ServiceDesk\DTO\RatingsStatsResponse;
use App\Domain\Dit\ServiceDesk\UseCase\GetRatingsStatsUseCase;

class GetRatingsStatsController
{
    public function __construct(
        private GetRatingsStatsUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'dit.serviceDesk.getRatingsStats',
        'Получить статистику по оценкам клиентов за указанный год',
        examples: [
            [
                'summary' => 'Получение статистики за год',
                'params'  => [
                    'year' => 2025,
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('год')]
        int $year
    ): RatingsStatsResponse {
        $stats = $this->useCase->getStats($year);

        return RatingsStatsResponse::build($stats);
    }
}
