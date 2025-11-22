<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\UseCase;

use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use App\Domain\CallCenter\UseDesk\Repository\MarkChatCommandRepository;
use App\Domain\CallCenter\UseDesk\Repository\MarkedChatsQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use DomainException;

class ToggleMarkChatUseCase
{
    public function __construct(
        private MarkChatCommandRepository $markChatCommandRepository,
        private MarkedChatsQueryRepository $markedChatsQueryRepository,
        private SecurityUser $currentUser
    ) {
    }

    public function markChat(int $chatId): MarkedChat
    {
        $existingMark = $this->markedChatsQueryRepository->findOneBy([
            'chat_id' => $chatId,
        ]);
        if ($existingMark instanceof MarkedChat) {
            throw new DomainException("чат с id = {$existingMark->chatId} уже отмечен");
        }

        $markChat = new MarkedChat(
            id: Loader::ID_FOR_INSERT,
            chatId: $chatId,
            markDate: new DateTimeImmutable(),
            markUserId: $this->currentUser->id
        );

        return $this->markChatCommandRepository->markChat($markChat);
    }

    public function unmarkChat(int $chatId): bool
    {
        $existingMark = $this->markedChatsQueryRepository->findOneBy([
            'chat_id' => $chatId,
        ]);
        if (!isset($existingMark)) {
            throw new DomainException("чат с id = $chatId не отмечен");
        }

        return $this->markChatCommandRepository->unmarkChat($existingMark);
    }
}
