<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\NominationWithRewardsResponse;
use App\Domain\Events\Rewards\DTO\ProgramResponse;
use App\Domain\Events\Rewards\DTO\ProgramWithNominationsResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('net.pd_prog')]
class Program
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
        #[Column(collectionOf: Nomination::class)] private array $nominations = []
    ) {
    }

    public function toProgramResponse(): ProgramResponse
    {
        return new ProgramResponse(
            id: $this->id,
            name: $this->name
        );
    }

    /** @return  NominationWithRewardsResponse[] */
    public function getNominations(): array
    {
        return array_values(
            array_map(
                fn (Nomination $nomination): NominationWithRewardsResponse => $nomination->toNominationWithRewardsResponse()
                , $this->nominations
            )
        );
    }

    public function toProgramWithNominationsResponse(): ProgramWithNominationsResponse
    {
        return new ProgramWithNominationsResponse(
            id: $this->id,
            name: $this->name,
            nominations_count: count($this->nominations),
            nominations: $this->getNominations()
        );
    }

    public function setNominations(array $nominations): void
    {
        $this->nominations = $nominations;
    }
}
