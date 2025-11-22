<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Password;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;

/**
 * @extends CommandRepository<Password>
 */
class PasswordCommandRepository extends CommandRepository
{
    protected string $entityName = Password::class;

    public function changePassword(Password $password): void
    {
        $this->conn->procedure('test.cp.set_password', [
            'i_id'       => $password->getUserId(),
            'i_password' => $password->getPassword(),
        ]);
    }

    public function markPasswordRecentlyChanged(Password $password): void
    {
        $this->conn->update('test.cp_emp',
            [
                'is_need_change_pass' => 0,
                'dt_last_pass_change' => new DateTimeImmutable(),
            ],
            [
                'id' => $password->getUserId(),
            ],
            [
                'dt_last_pass_change' => ParamType::DATE,
            ]
        );
    }
}
