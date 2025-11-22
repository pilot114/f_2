<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Repository;

use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use App\Domain\Marketing\CustomerHistory\Enum\Status;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<HistoryItem>
 */
class CustomerHistoryQueryRepository extends QueryRepository
{
    protected string $entityName = HistoryItem::class;

    /**
     * @return Enumerable<int, HistoryItem>
     */
    public function getData(
        ?string $q,
        ?Status $state,
        ?string $lang,
        ?DateTimeImmutable $dateFrom,
        ?DateTimeImmutable $dateTill,
        int $page = 1,
        int $perPage = 10
    ): Enumerable {
        $statePart = '';
        $langPart = '';
        $periodPart = '';
        $searchPart = '';

        if (!is_null($state)) {
            $statePart = "AND nsc.status = :state";
        }

        if (!is_null($lang)) {
            $langPart = "AND nsc.lang = :lang";
        }

        if (!is_null($dateFrom) && !is_null($dateTill)) {
            $periodPart = "AND (nsc.dt_created BETWEEN :start_date AND :end_date)";
        }

        if (!is_null($q) && trim($q) !== '') {
            $searchPart = "AND (LOWER(nsc.name) LIKE '%'||LOWER(:search)||'%' 
                      OR LOWER(nsc.contract) LIKE '%'||LOWER(:search)||'%' 
                      OR LOWER(nsc.preview) LIKE '%'||LOWER(:search)||'%')";
        }

        $offset = ($page - 1) * $perPage;

        $base = $this->query(
            "
          SELECT 
            nsc.id,
            nsc.dt_created AS create_dt,
            nsc.name AS employee_name,
            nsc.contract AS employee_contract,
            nsc.preview AS history_preview,
            nsc.text AS history,
            c1.name_ru AS write_country_name,
            city.name AS write_city_name,
            l.lang AS language_id,
            l.name_ru AS language_name,
            nsc.commentary,
            nsc.status AS state_id,
            CASE nsc.status WHEN 1 THEN 'На модерации' WHEN 2 THEN 'Опубликовано' WHEN 3 THEN 'Отказано' END AS state_name
          FROM test.nc_story_of_customer nsc
          JOIN test.ml_langs l ON l.lang = nsc.lang
          JOIN test.ml_cntrs c1 ON c1.cntr = nsc.shop
          JOIN sibvaleo.site_ruscity_city city ON city.id = nsc.city_id
          WHERE 1=1
            $statePart
            $langPart
            $periodPart
            $searchPart
          ORDER BY nsc.dt_created DESC, nsc.id DESC
          OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
        ",
            [
                'start_date' => $dateFrom,
                'end_date'   => $dateTill,
                'search'     => $q,
                'state'      => $state?->value,
                'lang'       => $lang,
                'offset'     => $offset,
                'limit'      => $perPage,
            ],
            [
                'start_date' => ParamType::DATE,
                'end_date'   => ParamType::DATE,
                'state'      => ParamType::INTEGER,
                'lang'       => ParamType::STRING,
                'search'     => ParamType::STRING,
                'offset'     => ParamType::INTEGER,
                'limit'      => ParamType::INTEGER,
            ]
        );

        $ids = $base->pluck('id');

        if (count($ids) === 0) {
            return $base;
        }

        $idsSql = implode('\',\'', $ids->toArray());

        $rows = iterator_to_array($this->conn->query(
            "
          SELECT nscls.story_id, nscls.shop, c2.name_ru
          FROM test.nc_story_of_customer_linked_shops nscls
          LEFT JOIN test.ml_cntrs c2 ON c2.cntr = nscls.shop
          WHERE nscls.story_id IN ('$idsSql')
          ORDER BY nscls.story_id, nscls.shop
        "
        ));

        $byStory = [];
        foreach ($rows as $r) {
            $sid = (int) $r['story_id'];
            $byStory[$sid][] = [
                'id'   => (string) $r['shop'],
                'name' => (string) $r['name_ru'],
            ];
        }

        $base->map(function (HistoryItem $row) use ($byStory): HistoryItem {
            $row->countries = $byStory[$row->id] ?? [];
            return $row;
        });

        return $base;
    }
}
