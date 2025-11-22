<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\CustomerHistory\Entity\Language;
use App\Domain\Marketing\CustomerHistory\UseCase\GetLanguagesUseCase;

readonly class GetLanguagesController
{
    public function __construct(
        private GetLanguagesUseCase $getLanguagesUseCase,
    ) {
    }

    /**
     * @return FindResponse<Language>
     */
    #[RpcMethod(
        'marketing.customerHistory.getLanguages',
        'Получение списка доступных языков',
        examples: [
            [
                'summary' => 'Список всех доступных языков',
                'params'  => [],
            ],
        ],
    )]
    public function getLanguages(): FindResponse
    {
        return new FindResponse($this->getLanguagesUseCase->getLanguages());
    }
}
