<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Controller;

use App\Common\Attribute\{RpcMethod, RpcParam};
use App\Domain\Dit\Reporter\Dto\ReportResponse;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

readonly class ExecuteReportController
{
    public function __construct(
        private ExecuteReportUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'dit.reporter.executeReport',
        'выполнение отчёта',
        examples: [
            [
                'summary' => 'выполнение отчёта с параметрами',
                'params'  => [
                    'id'    => 9012,
                    'input' => [
                        'rc' => 45,
                        'ds' => '01.06.2025',
                        'de' => '19.06.2025',
                    ],
                ],
            ],
        ]
    )]
    public function __invoke(
        #[RpcParam('id отчёта')]
        int $id,
        array $input,
    ): ReportResponse {
        ini_set('memory_limit', -1);

        [$items, $total] = $this->useCase->executeReport($id, $this->currentUser, $input);

        return new ReportResponse(
            $items,
            $this->useCase->reportQuery->keyField,
            $this->useCase->reportQuery->detailField,
            $this->useCase->reportQuery->masterField,
            $total,
        );
    }
}
