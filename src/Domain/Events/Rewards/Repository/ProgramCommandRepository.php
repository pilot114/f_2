<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Entity\Program;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

/**
 * @extends CommandRepository<Program>
 */
class ProgramCommandRepository extends CommandRepository
{
    protected string $entityName = Program::class;

    /**
     * @param Enumerable<int, Program> $programs
     */
    public function moveProgramsToGroup(Group $group, Enumerable $programs): void
    {
        $this->removeProgramsFromGroup($programs);
        $this->addProgramsToGroup($group, $programs);
    }

    /**
     * @param Enumerable<int, Program> $programs
     */
    private function removeProgramsFromGroup(Enumerable $programs): void
    {
        $sql = <<<SQL
                delete
                from NET.PD_PROG_GROUP pg
                where pg.pd_group_id in (
                select g.id
                from NET.PD_GROUP g
                where g.ID = pg.PD_GROUP_ID
                and g.GROUP_TYPE = 1)
                and pg.PD_PROG_ID in (:prog_id_list)
                and pg.PD_PRESENT_GDS_ID is null
            SQL;

        $this->conn->command($sql, [
            'prog_id_list' => $programs->map(fn (Program $program): int => $program->id)->values()->all(),
        ], [
            'prog_id_list' => ParamType::ARRAY_INTEGER,
        ]);
    }

    /**
     * @param Enumerable<int, Program> $programs
     */
    private function addProgramsToGroup(Group $group, Enumerable $programs): void
    {
        foreach ($programs as $program) {
            $this->conn->insert(
                'NET.PD_PROG_GROUP',
                [
                    'pd_group_id' => $group->id,
                    'pd_prog_id'  => $program->id,
                    'dt'          => new DateTimeImmutable(),
                ],
                [
                    'dt' => ParamType::DATE,
                ]
            );
        }
    }
}
