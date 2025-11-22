<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Repository;

use App\Domain\Marketing\CustomerHistory\Entity\Language;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Language>
 */
class LanguageQueryRepository extends QueryRepository
{
    protected string $entityName = Language::class;

    /**
     * @return Enumerable<int, Language>
     */
    public function getLanguages(): Enumerable
    {
        return $this->query("
            SELECT lang as id, name_ru as name
            FROM test.ml_langs
            ORDER BY name
        ");
    }
}
