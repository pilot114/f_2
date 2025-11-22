<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Controller;

use App\Common\Attribute\{RpcMethod, RpcParam};
use App\Domain\Dit\Reporter\UseCase\GetReportUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

readonly class GetReportController
{
    public function __construct(
        private GetReportUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'dit.reporter.getReport',
        'получение отчёта по id',
    )]
    /**
     * @return int[]
     */
    public function __invoke(
        #[RpcParam('id отчёта')]
        int $id,
    ): array {
        return $this->useCase->getReport($id, $this->currentUser)->toArray();
    }
}
