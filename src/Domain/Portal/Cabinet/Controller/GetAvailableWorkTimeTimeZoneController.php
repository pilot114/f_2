<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\UseCase\GetAvailableWorkTimeTimeZoneUseCase;

class GetAvailableWorkTimeTimeZoneController
{
    public function __construct(
        private GetAvailableWorkTimeTimeZoneUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.getAvailableWorkTimeTimeZone',
        'получение доступных для выбора временных зон рабочего времени сотрудника',
        examples: [
            [
                'summary' => 'получение доступных для выбора временных зон рабочего времени сотрудника',
                'params'  => [],
            ],
        ],
    )]
    /**
     * @return array<string>
     */
    public function __invoke(
    ): array {
        return $this->useCase->getList();
    }
}
