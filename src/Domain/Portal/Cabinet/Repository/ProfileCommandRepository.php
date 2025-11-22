<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Profile;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Profile>
 */
class ProfileCommandRepository extends CommandRepository
{
    protected string $entityName = Profile::class;

    public function updateInfo(Profile $profile): Profile
    {
        $this->conn->update(
            'test.cp_emp',
            [
                'telegram'          => $profile->getTelegram(),
                'office_phone_city' => $profile->getPhone(),
                'work_address'      => $profile->getCity(),
            ],
            [
                'id' => $profile->getUserId(),
            ]
        );

        $this->conn->update(
            'test.cp_emp_anketa',
            [
                'hide_birthday' => (int) $profile->getHideBirthday(),
            ],
            [
                'idemp' => $profile->getUserId(),
            ],
        );

        return $profile;
    }
}
