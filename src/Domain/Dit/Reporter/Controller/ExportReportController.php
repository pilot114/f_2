<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Controller;

use App\Common\Attribute\{RpcMethod, RpcParam};
use App\Common\Service\File\FileService;
use App\Domain\Dit\Reporter\UseCase\ExportReportUseCase;
use App\Domain\Portal\Files\Dto\FileResponse;
use App\Domain\Portal\Security\Entity\SecurityUser;

readonly class ExportReportController
{
    public function __construct(
        private ExportReportUseCase $useCase,
        private FileService $fileService,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'dit.reporter.exportReport',
        'выгрузка отчёта',
        examples: [
            [
                'summary' => 'выгрузка отчёта с параметрами',
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
    ): FileResponse {
        ini_set('memory_limit', -1);

        $file = $this->useCase->export($id, $input, $this->currentUser);
        $uploadedFile = $this->fileService->commonUpload($file, 'tmp');
        return $uploadedFile->toFileResponse();
    }
}
