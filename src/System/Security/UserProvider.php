<?php

declare(strict_types=1);

namespace App\System\Security;

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\WriteDatabaseInterface;
use Database\ORM\CommandRepository;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\DataMapperInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<SecurityUser>
 */
readonly class UserProvider implements UserProviderInterface
{
    /** @var CommandRepositoryInterface<SecurityUser> */
    private CommandRepositoryInterface $write;

    /**
     * @param DataMapperInterface<SecurityUser> $mapper
     */
    public function __construct(
        private SecurityQueryRepository $read,
        WriteDatabaseInterface          $write,
        DataMapperInterface             $mapper,
        // нужно для оптимизации
        private Container               $container,
    ) {
        $this->write = new CommandRepository($write, $mapper, SecurityUser::class);
    }

    /** @param SecurityUser $user */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->write->update($user);
    }

    public function supportsClass(string $class): bool
    {
        return true;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        /** @var ?SecurityUser $user */
        $user = $this->container->get(SecurityUser::class);
        if ($user !== null && $identifier === $user->email) {
            return $user;
        }

        $user = $this->read->findOneBy([
            'email' => $identifier,
        ]);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        /** @var SecurityUser $user */
        return $user;
    }
}
