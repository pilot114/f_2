<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\Entity\Language;
use App\Domain\Marketing\CustomerHistory\Repository\LanguageQueryRepository;
use Illuminate\Support\Enumerable;

readonly class GetLanguagesUseCase
{
    public function __construct(
        private LanguageQueryRepository $repository,
    ) {
    }

    /**
     * @return Enumerable<int, Language>
     */
    public function getLanguages(): Enumerable
    {
        return $this->repository->getLanguages();
    }
}
