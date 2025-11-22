<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Country;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Country>
 */
class CountryQueryRepository extends QueryRepository
{
    //@TODO Убрать этот список, когда будет готова задача https://jira.siberianhealth.com/browse/CP-467
    public const ALLOWED_COUNTRIES = [
        "АЗЕРБАЙДЖАН",
        "АРМЕНИЯ",
        "БЕЛАРУСЬ",
        "БОЛГАРИЯ",
        "БОСНИЯ И ГЕРЦЕГОВИНА",
        "ВЬЕТНАМ",
        "ГЕРМАНИЯ",
        "ГРУЗИЯ",
        "КАЗАХСТАН",
        "КЫРГЫЗСТАН",
        "МАКЕДОНИЯ",
        "МОЛДОВА",
        "МОНГОЛИЯ",
        "ПОЛЬША",
        "РОССИЯ",
        "СЕРБИЯ",
        "ТАДЖИКИСТАН",
        "ТАЙЛАНД",
        "ТУРЦИЯ",
        "УЗБЕКИСТАН",
        "УКРАИНА",
        "ЧЕРНОГОРИЯ",
        "ЧЕХИЯ",
    ];

    protected string $entityName = Country::class;

    /**
     * @param array<int> $ids
     * @return Enumerable<int, Country>
     */
    public function getByIds(array $ids): Enumerable
    {
        $sql = <<<SQL
                select 
                    c.country id, 
                    c.name          
                from tehno.country c
                where c.country IN (:ids)
            SQL;

        $countries = $this->query($sql, [
            'ids' => $ids,
        ], [
            'ids' => ParamType::ARRAY_INTEGER,
        ]);

        $foundCountryIds = $countries->map(fn ($country): int => $country->id)->all();
        $missingCountryIds = array_diff($ids, $foundCountryIds);

        if ($missingCountryIds !== []) {
            $missingIdsString = implode(', ', $missingCountryIds);
            throw new EntityNotFoundDatabaseException("Не найдены страны с id = ({$missingIdsString})");
        }

        return $countries;
    }

    public function findAll(): Enumerable
    {
        $sql = <<<SQL
                SELECT  DISTINCT c.country id, c.name
                FROM tehno.country c
                WHERE upper(c.name) IN (:country_names)
                ORDER BY c.name asc
            SQL;

        $countries = $this->query($sql,
            [
                'country_names' => self::ALLOWED_COUNTRIES,
            ],
            [
                'country_names' => ParamType::ARRAY_STRING,
            ]
        );

        return $countries->filter(function ($country): bool {
            if ($country->name !== "Босния и Герцеговина") {
                return true;
            }
            return $country->id === 702;
        });
    }
}
