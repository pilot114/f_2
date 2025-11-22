<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.point_sale_base.get_oo_country')]
class PartnerSaleStructure
{
    public function __construct(
        #[Column(name: 'id')] protected ?int            $id,
        #[Column(name: 'name')] protected ?string       $name,
        #[Column(name: 'currency')] protected ?string   $currency,
        #[Column(name: 'oo_percent')] protected ?string $percent,
        #[Column(name: 'oo')] protected ?string         $points,
    ) {
    }

    public static function fromDirtyValues(?int $id, string $name, ?string $currency, float $percent, float $points): self
    {
        return new self(
            $id,
            ucfirst(mb_convert_case($name, MB_CASE_LOWER, "UTF-8")),
            $currency ?? '',
            number_format(round($percent, 1), 1, '.', ' '),
            number_format(round($points, 1), 1, '.', ' '),
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getPercent(): ?string
    {
        return $this->percent;
    }

    public function getPoints(): ?string
    {
        return $this->points;
    }

    public function getHashKey(): string
    {
        return md5($this->getName() . $this->getCurrency());
    }

}
