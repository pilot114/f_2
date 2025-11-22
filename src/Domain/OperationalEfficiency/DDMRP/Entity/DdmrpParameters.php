<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParameters as DdmrpParametersDto;
use App\Domain\OperationalEfficiency\DDMRP\DTO\DdmrpParametersResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: self::TABLE_NAME, sequenceName: self::SQ_NAME)]
class DdmrpParameters
{
    public const TABLE_NAME = 'TEHNO.DDMRP_COK_PARAMETERS';
    public const SQ_NAME = 'TEHNO.DDMRP_COK_PARAMETERS_SQ';

    public function __construct(
        #[Column] private int $id,
        #[Column] private string $contract,

        /** Default Variability Factor -
         *  Степень отклонения от среднего значения
         */
        #[Column] private ?float $dvf,

        /** Default Lead Time Factor -
         *  Общий период времени от момента размещения заказа клиентом до момента получения им товара или услуги
         */
        #[Column] private ?int $dltf,

        /** Decoupled Lead Time -
         *  Количество дней от момента формирования заказа с РЦ Нск до постановки на остатки РЦ в стране
         */
        #[Column] private ?int $dlt,

        /** Reorder Point -
         *  Уровень запасов, при котором необходимо разместить новый заказ на пополнение запасов
         */
        #[Column(name: 're_order_point')] private ?int $reOrderPoint,

        /** Expiration Percent -
         *  Процент срока годности для вычисления минимального остаточного срока для ввоза в страну
         */
        #[Column(name: 'expiration_percent')] private ?int $expirationPercent,

        /** Minimum Order Quantity -
         *  Минимальный объём заказа
         */
        #[Column] private ?int $moq,

        /** Service Level Targets -
         * Количественные цели для конкретных показателей обслуживания,
         * которые должны быть достигнуты в определенный период времени
         */
        #[Column] private ?int $slt
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDvf(): ?float
    {
        return $this->dvf;
    }

    public function getDltf(): ?int
    {
        return $this->dltf;
    }

    public function getDlt(): ?int
    {
        return $this->dlt;
    }

    public function getReOrderPoint(): ?int
    {
        return $this->reOrderPoint;
    }

    public function getExpirationPercent(): ?int
    {
        return $this->expirationPercent;
    }

    public function getMoq(): ?int
    {
        return $this->moq;
    }

    public function getSlt(): ?int
    {
        return $this->slt;
    }

    public function toDdmrpParametersResponse(): DdmrpParametersResponse
    {
        return new DdmrpParametersResponse(
            dvf: $this->dvf,
            dltf: $this->dltf,
            dlt: $this->dlt,
            reOrderPoint: $this->reOrderPoint,
            expirationPercent: $this->expirationPercent,
            moq: $this->moq,
            slt: $this->slt,
        );
    }

    public function update(DdmrpParametersDto $dto): void
    {
        $this->dvf = $dto->dvf;
        $this->dltf = $dto->dltf;
        $this->dlt = $dto->dlt;
        $this->reOrderPoint = $dto->reOrderPoint;
        $this->expirationPercent = $dto->expirationPercent;
        $this->moq = $dto->moq;
        $this->slt = $dto->slt;
    }

    public function isRecordExists(): bool
    {
        return $this->id > 0;
    }

    public function getContract(): string
    {
        return $this->contract;
    }
}
