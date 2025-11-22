<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\UseCase;

use App\Domain\Hr\Reinstatement\Entity\Employee;
use App\Domain\Hr\Reinstatement\Repository\ReinstatementCommandRepository;
use App\Domain\Hr\Reinstatement\Repository\ReinstatementQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\CpConnection;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

class ReinstatementUseCase
{
    public function __construct(
        private ReinstatementQueryRepository $queryRepository,
        private ReinstatementCommandRepository $commandRepository,
        private SecurityUser $currentUser,
        private CpConnection $connection
    ) {
    }

    /**
     * @return  Enumerable<int, Employee>
     */
    public function getEmployeeByNamePart(string $query): Enumerable
    {
        return $this->queryRepository->getByNamePart($query);
    }

    public function reinstateEmployee(int $employeeId): bool
    {
        $result = (bool) $this->commandRepository->reinstateEmployee($employeeId, $this->currentUser->id);
        if ($result) {
            $user = $this->connection->query("SELECT * FROM test.cp_emp WHERE id = $employeeId")->current();
            $editor = $this->connection->query("SELECT * FROM test.cp_emp WHERE id = " . $this->currentUser->id)->current();
            if ($user === [] && $editor === []) {
                return $result;
            }
            $email = $editor['email'];
            $subject = $user['name'] . ' ' . (new DateTimeImmutable($user['date_update']))->format("d.m.Y H:i");
            $text = "
                Вы восстановили учетную запись уволенного сотрудника: " . $user['name'] . ". <br>
                Логин: " . $user['login'] . "<br>
                Новый пароль: " . $user['pw'] . "<br>
                Пожалуйста, обновите информацию о восстановленном сотруднике. Проверьте отдел, адрес электронной почты, должность и другие данные.<br>
                Для сотрудников УК в сервисе: <a href=\"https://cp.siberianhealth.com/personal/emp.php\">Сотрудники компании</a><br>
                Для сотрудников ЦОКа в сервисе: <a href=\"https://cp.siberianhealth.com/orp/cok/index.php\">Управление ЦОКами</a><br>";
            $this->connection->insert('test.cp_mail',
                [
                    'email'   => $email,
                    'subject' => $subject,
                    'txt'     => $text,
                    'alias'   => "RESTORE_EMPLOYEE",
                ]);

        }
        return $result;
    }
}
