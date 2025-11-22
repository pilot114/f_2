<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Events\Rewards\DTO\GroupWithProgramsResponse;
use App\Domain\Events\Rewards\DTO\ProgramWithNominationsResponse;
use App\Domain\Events\Rewards\Enum\GroupType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'net.pd_group', sequenceName: 'net.pd_group_sq')]
class Group
{
    public const UNALLOCATED_PROGRAMS_NAME = 'Нераспределенные программы';

    public function __construct(
        #[Column(name: 'id')] public readonly int $id,
        #[Column(name: 'name')] private string $name,
        #[Column(collectionOf: Program::class)] private array $programs = [],
        #[Column(name: 'group_type')] public GroupType $type = GroupType::GROUP,
    ) {
        if ($this->type !== GroupType::GROUP) {
            throw new InvariantDomainException('параметр type для группы может быть равен только 1');
        }
    }

    public function getName(): string
    {
        return $this->name ?: self::UNALLOCATED_PROGRAMS_NAME;
    }

    public function getPrograms(): array
    {
        return array_values(
            array_map(
                fn (Program $program): ProgramWithNominationsResponse => $program->toProgramWithNominationsResponse(),
                $this->programs
            )
        );
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function toGroupWithProgramsResponse(): GroupWithProgramsResponse
    {
        return new GroupWithProgramsResponse(
            id: $this->id,
            name: $this->name,
            programs_count: count($this->programs),
            programs: $this->getPrograms()
        );
    }
}
