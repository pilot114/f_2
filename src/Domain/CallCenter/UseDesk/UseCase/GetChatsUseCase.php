<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\CallCenter\UseDesk\Entity\Chat;
use App\Domain\CallCenter\UseDesk\Entity\Client;
use App\Domain\CallCenter\UseDesk\Entity\Message;
use App\Domain\CallCenter\UseDesk\Enum\StatusType;
use App\Domain\CallCenter\UseDesk\Enum\UserType;
use App\Domain\CallCenter\UseDesk\Repository\MarkedChatsQueryRepository;
use App\Domain\CallCenter\UseDesk\Service\UseDeskHttpClient;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Enumerable;

class GetChatsUseCase
{
    public const CHATS_PER_PAGE = -1;

    public function __construct(
        private UseDeskHttpClient $client,
        private MarkedChatsQueryRepository $markedChatsQueryRepository,
    ) {
    }

    /** @return Enumerable<int, Chat> */
    public function getChats(bool $markedOnly, bool $noAnswer): Enumerable
    {
        $chatsRaw = $this->getChatsData();

        $chats = EnumerableWithTotal::build();

        foreach ($chatsRaw as $item) {
            $chat = $this->makeChat($item);
            $chatMessages = $this->sortMessages($item['messages']);

            $hasRecentMessage = false;

            foreach ($chatMessages as $message) {
                $message = $this->makeMessage($chat->id, $message);
                if ($message->userType === UserType::TRIGGER) {
                    continue;
                }
                $hasRecentMessage = true;
                $chat->addMessage($message);
            }

            // только сообщения за последние N дней
            if ($hasRecentMessage) {
                $chats->add($chat);
                if (isset($message)) {
                    $chat->setHasAnswer($message->isSendByUser());
                }
            }
        }

        $this->setMarkedChats($chats);

        if ($markedOnly) {
            $chats = $chats->filter(fn (Chat $chat): bool => $chat->isMarkedChat());
        }

        if ($noAnswer) {
            $chats = $chats->filter(fn (Chat $chat): bool => !$chat->isHasAnswer());
        }

        return $chats->sortBy(fn (Chat $chat): DateTimeImmutable => $chat->getFirstMessage()->date);
    }

    protected function getChatsData(): array
    {
        $fromDate = (new DateTimeImmutable())->modify('-7 days');

        $chats = $this->client->getChats(
            [
                'per_page'            => self::CHATS_PER_PAGE,
                'have_messages_since' => $fromDate->format('Y-m-d'),
                'with_messages'       => 1,
            ]
        );

        $chatsData = $chats['data'];
        $lastPage = $chats['meta']['last_page'];
        for ($i = $chats['meta']['current_page'] + 1; $i <= $lastPage; $i++) {
            $tmp = $this->client->getChats(
                [
                    'page'                => $i,
                    'per_page'            => self::CHATS_PER_PAGE,
                    'have_messages_since' => $fromDate->format('d.m.Y'),
                    'with_messages'       => 1,
                ]
            );
            $chatsData = array_merge($chatsData, $tmp['data']);
        }

        return $chatsData;
    }

    protected function sortMessages(array $messagesData): array
    {
        usort($messagesData, function (array $a, array $b): int {
            return (new DateTimeImmutable($a['created_at'])) <=> (new DateTimeImmutable($b['created_at']));
        });

        return $messagesData;
    }

    protected function makeMessage(int $chatId, array $messageData): Message
    {
        $userType = UserType::tryFrom($messageData['from']);

        if (!isset($userType)) {
            throw new DomainException('у сообщения обязательно должен быть тип пользователя');
        }

        return new Message(
            id: $messageData['id'],
            chatId: $chatId,
            userType: $userType,
            text: $messageData['text'],
            date: new DateTimeImmutable($messageData['created_at'])
        );
    }

    protected function makeChat(array $chatData): Chat
    {
        return new Chat(
            id: $chatData['id'],
            status: ($chatData['status']) ? StatusType::tryFrom($chatData['status']) : null,
            client: new Client(
                id: $chatData['client']['id'],
                name: $chatData['client']['name'])
        );
    }

    /** @param  Enumerable<int, Chat> $chats */
    private function setMarkedChats(Enumerable $chats): void
    {
        $markedChats = [];

        if (!$chats->isEmpty()) {
            $markedChats = $this->markedChatsQueryRepository->findBy([
                'chat_id' => $chats->pluck('id')->toArray(),
            ]);
        }

        foreach ($markedChats as $markedChat) {
            /** @var ?Chat $chat */
            $chat = $chats->firstWhere('id', $markedChat->chatId);

            if ($chat instanceof Chat) {
                $chat->setIsMarkedChat(true);
            }
        }
    }
}
