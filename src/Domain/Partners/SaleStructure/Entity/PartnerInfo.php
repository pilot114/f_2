<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('net.web.cursors.cr_personal_lider_info')]
class PartnerInfo
{
    public function __construct(
        #[Column(name: 'id')] protected ?int                          $id,
        #[Column(name: 'name')] protected string                      $name,
        #[Column(name: 'contract')] protected string                  $contract,
        #[Column(name: 'country')] protected ?string                  $countryName,
        #[Column(name: 'country_code')] protected ?int                $countryCode,
        #[Column(name: 'rang')] protected int                         $rank,
        #[Column(name: 'd_end')] protected ?DateTimeImmutable         $dateEnd,
        #[Column(name: 'win_dt_career')] protected ?DateTimeImmutable $dateRankAssigned,
        #[Column(name: 'rank_name')] protected ?string                $rankName,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContract(): string
    {
        return $this->contract;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getCountryCode(): ?int
    {
        return $this->countryCode;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function getDateRankAssigned(): ?DateTimeImmutable
    {
        return $this->dateRankAssigned;
    }

    public function getRankName(): ?string
    {
        return $this->rankName;
    }

}
