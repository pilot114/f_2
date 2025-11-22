<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Password;
use App\Domain\Portal\Cabinet\Repository\PasswordCommandRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChangePasswordUseCase
{
    public function __construct(
        /** @var QueryRepositoryInterface<Password> */
        private QueryRepositoryInterface $read,
        private PasswordCommandRepository $write,
        private TransactionInterface $transaction,
    ) {
    }

    public function changePassword(int $userId, string $old, string $new): void
    {
        $password = $this->read->findOneBy([
            'id' => $userId,
        ]);

        if ($password === null) {
            throw new NotFoundHttpException('Пароль пользователя не найден');
        }

        $password->changePassword($old, $new);

        $this->transaction->beginTransaction();
        $this->write->changePassword($password);
        $this->write->markPasswordRecentlyChanged($password);
        $this->transaction->commit();
    }
}
