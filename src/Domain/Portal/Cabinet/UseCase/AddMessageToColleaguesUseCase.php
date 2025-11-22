<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\DTO\AddMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Cabinet\Repository\UserQueryRepository;
use App\Domain\Portal\Cabinet\Service\ColleagueEmailer;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;

class AddMessageToColleaguesUseCase
{
    public function __construct(
        private MessageToColleaguesQueryRepository   $messageQueryRepository,
        private MessageToColleaguesCommandRepository $messageCommandRepository,
        private MessageToColleaguesNotificationCommandRepository $notificationCommandRepository,
        private UserQueryRepository $userQueryRepository,
        private SecurityUser $currentUser,
        private ColleagueEmailer $emailer,
        private TransactionInterface $transaction,
        private RpcClient $rpcClient,
    ) {
    }

    public function add(AddMessageToColleaguesRequest $request): MessageToColleagues
    {
        $user = new User($this->currentUser->id, $this->currentUser->name, $this->currentUser->email);
        $message = $this->messageQueryRepository->findMessageToColleagues($user->id);

        /** Если дата конца показа сообщения меньше текущей (сообщение осталось в прошлом),
        то такое сообщение и список рассылки по нему надо удалять и создавать всё заново */
        $this->transaction->beginTransaction();
        if ($message instanceof MessageToColleagues && !$message->isActual()) {
            $this->notificationCommandRepository->deleteNotifications($message->getId());
            $this->messageCommandRepository->delete($message->getId());
            $message = null;
        }

        if (!$message instanceof MessageToColleagues) {
            $message = $this->createMessage($user, $request->message, $request->startDate, $request->endDate);
        } else {
            $this->editMessage($message, $request->message, $request->startDate, $request->endDate);
        }

        $this->notificationCommandRepository->deleteNotifications($message->getId());
        $this->createNotifications($message, $request->notifyUserIds);

        $this->transaction->commit();

        $this->rpcClient->call(
            'Department.editCachedUserData',
            [
                'userId'     => $this->currentUser->id,
                'parameters' => [
                    'hasActiveMessage' => $message->isActive(),
                ],
            ]
        );
        $this->sendNotification($message);

        return $message;
    }

    private function sendNotification(MessageToColleagues $message): void
    {
        $emails = $message->getNotificationEmailsList();

        if ($emails !== []) {
            $this->emailer->send(
                $emails,
                $message,
                $this->currentUser->name
            );
        }
    }

    /** @param int[] $notifyUserIds*/
    private function createNotifications(MessageToColleagues $message, array $notifyUserIds): void
    {
        $notifyUsers = [];
        if ($notifyUserIds !== []) {
            $notifyUsers = $this->userQueryRepository->getUsersByIds($notifyUserIds);
        }

        foreach ($notifyUsers as $userToNotify) {
            $notification = $this->notificationCommandRepository->create(
                new MessageToColleaguesNotification(
                    Loader::ID_FOR_INSERT,
                    $message->getId(),
                    $userToNotify
                )
            );
            $message->addNotification($notification);
        }
    }

    private function createMessage(User $user, string $messageText, DateTimeImmutable $startDate, DateTimeImmutable $endDate): MessageToColleagues
    {
        $message = new MessageToColleagues(
            Loader::ID_FOR_INSERT,
            $user,
            $messageText,
            $startDate->setTime(0, 0, 0),
            $endDate->setTime(23, 59, 59),
            new DateTimeImmutable(),
        );

        return $this->messageCommandRepository->create($message);
    }

    private function editMessage(MessageToColleagues $message, string $messageText, DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        $message->edit($messageText, $startDate, $endDate);

        $this->messageCommandRepository->update($message);
    }
}
