<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Dit\Gifts\DTO\GetCertificatesListResponse;
use App\Domain\Dit\Gifts\UseCase\GetCertificatesListUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetCertificatesListController
{
    public function __construct(
        private GetCertificatesListUseCase $useCase,
        private SecurityQueryRepository $securityRepository,
        private SecurityUser $securityUser
    ) {
        $code = 'certificate-management';
        $hasAccess = $this->securityRepository->hasCpMenu($this->securityUser->id, $code);
        if (!$hasAccess) {
            throw new AccessDeniedHttpException("Нет прав на cp_menu: $code");
        }
    }

    #[RpcMethod(
        'dit.gifts.getCertificatesList',
        'список сертификатов',
        examples: [
            [
                'summary' => 'список сертификатов',
                'params'  => [
                    'search' => '55',
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('поиск по сертификату, фактуре, контракту')]
        ?string $search = null
    ): GetCertificatesListResponse {
        $certificates = $search ? $this->useCase->getCertificatesList($search) : EnumerableWithTotal::build();

        return GetCertificatesListResponse::build($certificates);
    }
}
