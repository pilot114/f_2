<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<MessageToColleagues>
 */
class MessageToColleaguesQueryRepository extends QueryRepository
{
    protected string $entityName = MessageToColleagues::class;

    public function findMessageToColleagues(int $userId): ?MessageToColleagues
    {
        $sql = <<<SQL
                     SELECT
                        m.id,
                        m.message,
                        m.message_start_date,
                        m.message_end_date,
                        m.message_change_date,
                        ----------------------
                        e.id cp_emp_id,
                        e.name cp_emp_name,
                        e.email cp_emp_email
                    FROM
                        test.cp_emp_messages m
                    JOIN test.cp_emp e ON e.id = m.cp_emp
                    WHERE 
                        1=1
                        and m.cp_emp = :userId
        
SQL;
        return $this->query($sql, [
            'userId' => $userId,
        ]
        )->first();
    }
}
