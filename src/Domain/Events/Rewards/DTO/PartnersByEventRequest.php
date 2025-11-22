<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class PartnersByEventRequest
{
    public function __construct(
        #[RpcParam('id мероприятия')]
        public readonly int                $eventId,
        #[RpcParam('id страны партнёра по цок')]
        public readonly FilterOption|int   $country,
        #[RpcParam('найти партнёров у которых есть нарушения')]
        public readonly bool               $hasPenalty,
        #[RpcParam('статус партнёра')]
        public ?PartnerStatusType          $partnerStatus = null,
        #[RpcParam('список id программ')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        /** @var array<int> $programIds */
        public readonly array $programIds = [],
        #[RpcParam('список id номинаций')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        /** @var array<int> $nominationIds */
        public readonly ?array $nominationIds = [],
        #[RpcParam('список id наград')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        /** @var array<int> $rewardIds */
        public readonly ?array $rewardIds = [],
        #[RpcParam('фильтр начала периода номинации на награду')]
        public readonly ?DateTimeImmutable $nominationStartDate = null,
        #[RpcParam('фильтр конца периода номинации на награду')]
        public readonly ?DateTimeImmutable $nominationEndDate = null,
        #[RpcParam('поиск по контракту или ФИО партнёра')]
        #[Assert\Length(max: 100)]
        public readonly string $search = '',
        #[RpcParam('сортировка по имени партнёра: ASC|DESC')]
        public readonly string $sortByName = 'ASC',
        #[RpcParam('номер страницы (с 1)')]
        #[Assert\Positive]
        public readonly int $page = 1,
        #[RpcParam('кол-во партнёров на странице')]
        #[Assert\Range(
            min: 0,
            max: 1000
        )]
        public readonly int $perPage = 50,
    ) {
    }
}
