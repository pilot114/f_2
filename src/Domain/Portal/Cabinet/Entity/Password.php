<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use App\Common\Exception\InvariantDomainException;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_emp', sequenceName: 'net.sq_cp_emp')]
class Password
{
    public function __construct(
        #[Column(name: 'id')] private int $id,
        #[Column(name: 'pw')] private string $password,
    ) {
    }

    public function changePassword(string $old, string $new): void
    {
        if ($this->password !== $old) {
            throw new InvariantDomainException('Старый пароль введен неверно');
        }

        if ($this->password === $new) {
            throw new InvariantDomainException('Новый пароль не должен совпадать со старым');
        }

        $this->password = $new;
    }

    public function getUserId(): int
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
