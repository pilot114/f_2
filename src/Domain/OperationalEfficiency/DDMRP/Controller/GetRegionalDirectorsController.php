<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\OperationalEfficiency\DDMRP\DTO\RegionDirectorResponse;
use App\Domain\OperationalEfficiency\DDMRP\Entity\RegionalDirector;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetRegionalDirectorsUseCase;

class GetRegionalDirectorsController
{
    public function __construct(
        private GetRegionalDirectorsUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<RegionDirectorResponse>
     */
    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.getRegionalDirectors',
        summary: 'Получить список региональных директоров',
        examples: [
            [
                'summary' => 'Получить список региональных директоров',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(): FindResponse
    {
        $regionalDirectors = $this->useCase
            ->getRegionalDirectors()
            ->map(fn (RegionalDirector $item): RegionDirectorResponse => $item->toRegionDirectorResponse());

        return new FindResponse($regionalDirectors);
    }
}
