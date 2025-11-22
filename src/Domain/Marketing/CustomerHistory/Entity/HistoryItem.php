<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: HistoryItem::TABLE)]
class HistoryItem
{
    public const TABLE = 'net.nc_story_of_customer';

    public function __construct(
        #[Column(name: 'id')] public readonly int $id,
        #[Column(name: 'create_dt')] public readonly ?DateTimeImmutable $createDt,
        #[Column(name: 'history_preview')] public readonly ?string $historyPreview,
        #[Column(name: 'history')] public readonly ?string $history,
        #[Column(name: 'commentary')] public readonly ?string $commentary,
        #[Column(name: 'write_country_name')] public readonly ?string $writeCountryName,
        #[Column(name: 'write_city_name')] public readonly ?string $writeCityName,
        #[Column(name: 'language')] public ?Language $lang,
        #[Column(name: 'state')] public ?State $state,
        #[Column(name: 'employee')] public ?Employee $employee,
        #[Column(collectionOf: Country::class)] public ?array $countries = [],
    ) {
    }

}
