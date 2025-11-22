<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class PartnerFullInfoRequest
{
    public function __construct(
        #[RpcParam('Id партнёра')]
        public readonly int $partnerId,
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
        #[RpcParam('имеются ли у партнёра нарушения')]
        public readonly ?bool               $hasPenalty = null,
        #[RpcParam('статус партнёра')]
        public ?PartnerStatusType $partnerStatus = null,
        #[RpcParam('Скрывать удалённые награды')]
        public bool $hideDeletedRewards = true,
        #[RpcParam('id события(для режима по событию)')]
        public ?int $eventId = null
    ) {
    }
}
