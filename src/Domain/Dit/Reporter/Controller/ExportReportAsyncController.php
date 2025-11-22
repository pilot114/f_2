<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Controller;

use App\Common\Attribute\{RpcMethod, RpcParam};
use App\Domain\Dit\Reporter\Message\ExportReportMessage;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ExportReportAsyncController
{
    public function __construct(
        private MessageBusInterface $bus,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'dit.reporter.exportReportAsync',
        'выгрузка отчёта в асинхронном режиме (результат на email)',
        examples: [
            [
                'summary' => 'выгрузка отчёта с параметрами в очередь',
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
    ): bool {
        // Отправляем задачу в очередь
        $this->bus->dispatch(new ExportReportMessage(
            reportId: $id,
            input: $input,
            userId: $this->currentUser->id,
            userEmail: $this->currentUser->email,
        ));
        return true;
    }
}
