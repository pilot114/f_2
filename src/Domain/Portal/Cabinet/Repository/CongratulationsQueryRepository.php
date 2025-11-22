<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Congratulation;
use Database\ORM\QueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<Congratulation>
 */
class CongratulationsQueryRepository extends QueryRepository
{
    protected string $entityName = Congratulation::class;

    /**
     * @return Enumerable<int, Congratulation>
     */
    public function findCongratulationsByReceiverId(int $receiverId, DateTimeImmutable $startFrom): Enumerable
    {
        $sql = <<<SQL
                     SELECT
                        b.id,
                        sender.id from_user_id,
                        sender.name from_user_name,
                        TO_DATE(b.DATEYEAR, 'YYYY') as "year",
                        b.TXT message,
                        files.FPATH
                    FROM
                        test.UT_BIRTHDAY_MSG b
                    LEFT JOIN test.cp_emp sender ON sender.id = b.idemp_from
                    LEFT JOIN test.cp_files files ON sender.id = files.parentid
                    WHERE
                        b.idemp_to = :receiverId
                        AND sender.id = b.idemp_from
                        AND dateyear <= :year
                        AND parent_tbl = 'userpic'
                    ORDER BY b.dateyear DESC
SQL;

        return $this->query($sql, [
            'receiverId' => $receiverId,
            'year'       => (int) $startFrom->format('Y'),
        ]
        );
    }
}
