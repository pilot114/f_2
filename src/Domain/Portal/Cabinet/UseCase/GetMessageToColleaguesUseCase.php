<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\DTO\GetMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationQueryRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;

class GetMessageToColleaguesUseCase
{
    public function __construct(
        private MessageToColleaguesQueryRepository $messageQueryRepository,
        private MessageToColleaguesNotificationQueryRepository $notificationQueryRepository,
        private SecurityUser $currentUser,
    ) {
    }

    public function get(GetMessageToColleaguesRequest $request): ?MessageToColleagues
    {
        $message = $this->messageQueryRepository->findMessageToColleagues($request->userId);

        if (!$message instanceof MessageToColleagues) {
            return null;
        }

        $isAuthor = $this->currentUser->id === $request->userId;

        // Если сообщение устарело (дата окончания прошла) - не показываем никому
        if (!$message->isActual()) {
            return null;
        }

        // Если сообщение еще не началось (дата начала в будущем), Показываем только автору сообщения
        if ($message->isInFuture() && !$isAuthor) {
            return null;
        }

        // Загружаем уведомления только если текущий пользователь - автор сообщения
        if ($isAuthor) {
            $notifications = $this->notificationQueryRepository->getNotificationsList($message->getId());
            foreach ($notifications as $notification) {
                $message->addNotification($notification);
            }
        }

        return $message;
    }
}
