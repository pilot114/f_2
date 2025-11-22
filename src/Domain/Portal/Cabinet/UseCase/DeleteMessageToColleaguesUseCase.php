<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;

class DeleteMessageToColleaguesUseCase
{
    public function __construct(
        private MessageToColleaguesQueryRepository $messageQueryRepository,
        private MessageToColleaguesCommandRepository $messageCommandRepository,
        private MessageToColleaguesNotificationCommandRepository $notificationCommandRepository,
        private SecurityUser $currentUser,
        private RpcClient $rpcClient,
    ) {
    }

    public function delete(): bool
    {
        $message = $this->messageQueryRepository->findMessageToColleagues($this->currentUser->id);

        if (!$message instanceof MessageToColleagues) {
            return false;
        }

        $this->notificationCommandRepository->deleteNotifications($message->getId());
        $this->messageCommandRepository->delete($message->getId());

        $this->rpcClient->call(
            'Department.editCachedUserData',
            [
                'userId'     => $this->currentUser->id,
                'parameters' => [
                    'hasActiveMessage' => false,
                ],
            ]
        );
        return true;
    }
}
