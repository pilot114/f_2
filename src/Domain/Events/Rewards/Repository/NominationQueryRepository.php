<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Nomination;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Nomination>
 */
class NominationQueryRepository extends QueryRepository
{
    protected string $entityName = Nomination::class;

    /** @return Enumerable<int, Nomination> */
    public function getNominationsForVerificationFilter(array $programIds): Enumerable
    {
        $sql = "
        select 
        pn.id,
        pn.name,
        pp.id program_id,
        pp.name program_name
        from net.pd_nominations pn
        join net.pd_prog pp on pp.id = pn.prog
        where pn.prog in (:program_id_list)
        order by pn.name
        ";

        return $this->query($sql, [
            'program_id_list' => $programIds,
        ], [
            'program_id_list' => ParamType::ARRAY_INTEGER,
        ]);
    }
}
