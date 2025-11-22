<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Program>
 */
class ProgramQueryRepository extends QueryRepository
{
    protected string $entityName = Program::class;

    /**
     * @param array<int> $ids
     * @return Enumerable<int, Program>
     */
    public function getByIds(array $ids): Enumerable
    {
        $sql = <<<SQL
                select * 
                from net.pd_prog pp
                where pp.id IN (:ids)
            SQL;

        $programs = $this->query($sql, [
            'ids' => $ids,
        ], [
            'ids' => ParamType::ARRAY_INTEGER,
        ]);

        $foundProgramIds = $programs->map(fn (Program $program): int => $program->id)->all();
        $missingProgramIds = array_diff($ids, $foundProgramIds);

        if ($missingProgramIds !== []) {
            $missingIdsString = implode(', ', $missingProgramIds);
            throw new EntityNotFoundDatabaseException("Не найдены программы с id = ({$missingIdsString})");
        }

        return $programs;
    }

    /**
     * @param Enumerable<int, Reward> $rewards
     * @return Enumerable<int, Program>
     */
    public function getByRewards(Enumerable $rewards): Enumerable
    {
        $sql = <<<SQL
                select pp.id, pp.name, nom.id nominations_id, nom.name nominations_name
                from net.pd_prog pp
                join net.pd_nominations nom on nom.prog = pp.id
                join net.pd_nomination_presents np on np.nomination = nom.id
                join net.pd_present_gds gds on gds.nomination_pr = np.id
                where gds.id IN (:rewardIds)
            SQL;

        $rewardIds = $rewards->map(fn (Reward $reward): int => $reward->id)->values()->all();
        $programs = $this->query($sql, [
            'rewardIds' => $rewardIds,
        ], [
            'rewardIds' => ParamType::ARRAY_INTEGER,
        ]);

        if ($programs->isEmpty()) {
            throw new EntityNotFoundDatabaseException('Программ не найдено. Награды должны относится хотя бы к одной программе');
        }

        return $programs;
    }

    /** @return Enumerable<int, Program> */
    public function getProgramsForVerificationFilter(): Enumerable
    {
        $sql = "select pp.id,
                       pp.name
                from net.pd_prog pp
                join net.pd_prog_group ppg
                on ppg.pd_prog_id = pp.id --только в группе
                join net.pd_group pg
                on pg.id = ppg.pd_group_id
                and pg.group_type = 1
                 
                where pp.id != 6438347 --не \"Звездный наставник\"
                    and ppg.pd_group_id != 3 --не в гр.\"Архив\"
                order by pp.name
            ";
        return $this->query($sql);
    }
}
