<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity('test.cp_emp')]
class SecurityUser implements UserInterface
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
        #[Column] public readonly string $email,
        #[Column] public readonly string $login,
        #[Column(collectionOf: Role::class)]
        private array $roles = [],
        #[Column(collectionOf: Permission::class)]
        private array $permissions = [],
    ) {
    }

    public function getRoles(): array
    {
        if ($this->roles === []) {
            return ['ROLE_USER'];
        }
        $roles = array_map(
            static fn (Role $x): string => $x->getName(),
            $this->roles
        );
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function __toString(): string
    {
        return "$this->id: $this->name";
    }
}
