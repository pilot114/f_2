<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

use App\Common\Attribute\RpcParam;
use App\Common\DTO\FilterOption;

readonly class GetGroupRequest
{
    public function __construct(
        #[RpcParam('Поиск по стране')]
        public int|FilterOption $country,

        #[RpcParam('Поиск по названию программы, номинации, категории')]
        public ?string $search = null,

        #[RpcParam('Поиск по типу наград')]
        /** @var RewardTypeRequest[] $rewardTypes */
        public array $rewardTypes = [],

        #[RpcParam('Поиск по статусу true - актуальные, false - архивные, null - любой статус')]
        public bool $status = false,
    ) {
    }
}
