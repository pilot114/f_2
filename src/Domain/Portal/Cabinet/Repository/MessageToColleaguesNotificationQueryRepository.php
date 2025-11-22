<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<MessageToColleaguesNotification>
 */
class MessageToColleaguesNotificationQueryRepository extends QueryRepository
{
    protected string $entityName = MessageToColleaguesNotification::class;

    /**
     * @return Enumerable<int, MessageToColleaguesNotification>
     */
    public function getNotificationsList(int $messageId): Enumerable
    {
        $sql = <<<SQL
                     SELECT
                        n.id,
                        n.message_id,
                        ----------------------
                        e.id cp_emp_to_id,
                        e.name cp_emp_to_name,
                        e.email cp_emp_to_email,
                        ------------------------
                        r.id cp_emp_to_responses_id,
                        r.name cp_emp_to_responses_name
                    FROM
                        test.CP_EMP_MESSAGE_USERS n
                    JOIN test.cp_emp e ON e.id = n.cp_emp_to
                    left join test.cp_emp_state es on es.employee = e.id and es.is_main = 1
                    left join test.cp_depart_state ds on ds.id = es.state
                    left join test.cp_response r on r.id = ds.response
                    WHERE 
                        1=1
                        and n.message_id = :messageId
        
SQL;
        return $this->query($sql, [
            'messageId' => $messageId,
        ]
        );
    }

    public function getNotification(int $id): MessageToColleaguesNotification
    {
        $sql = <<<SQL
                     SELECT
                        n.id,
                        n.message_id,
                        ----------------------
                        e.id cp_emp_to_id,
                        e.name cp_emp_to_name,
                        e.email cp_emp_to_email
                    FROM
                        test.CP_EMP_MESSAGE_USERS n
                    JOIN test.cp_emp e ON e.id = n.cp_emp_to
                    WHERE 
                        1=1
                        and n.id = :id
        
SQL;
        return $this->query($sql, [
            'id' => $id,
        ]
        )->firstOrFail();
    }
}
