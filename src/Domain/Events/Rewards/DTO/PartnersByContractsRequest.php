<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class PartnersByContractsRequest
{
    public function __construct(
        #[RpcParam('id страны')]
        public readonly FilterOption|int $country,
        #[RpcParam('список контрактов')]
        #[Assert\All([
            new Assert\Type('string', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        /** @var array<string> $contracts */
        public readonly array $contracts,
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
        public readonly array $nominationIds = [],
        #[RpcParam('список id наград')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        /** @var array<int> $rewardIds */
        public readonly array $rewardIds = [],
        #[RpcParam('статус состояния выдачи награды')]
        public readonly ?RewardIssuanceStateStatusType $rewardIssuanceState = null,
        #[RpcParam('фильтр начала периода номинации на награду')]
        public readonly ?DateTimeImmutable             $nominationStartDate = null,
        #[RpcParam('фильтр конца периода номинации на награду')]
        public readonly ?DateTimeImmutable             $nominationEndDate = null,
        #[RpcParam('фильтр начала периода выдачи награды')]
        public readonly ?DateTimeImmutable             $rewardStartDate = null,
        #[RpcParam('фильтр конца периода выдачи награды')]
        public readonly ?DateTimeImmutable             $rewardEndDate = null,
        public readonly bool                           $withPenalties = false,
        #[RpcParam('Скрывать удалённые награды')]
        public readonly bool                           $hideDeletedRewards = true,
    ) {
    }
}
