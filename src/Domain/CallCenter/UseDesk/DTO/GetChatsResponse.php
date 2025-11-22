<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\DTO;

use App\Domain\CallCenter\UseDesk\Entity\Chat;
use App\Domain\CallCenter\UseDesk\Entity\Message;
use Illuminate\Support\Enumerable;

class GetChatsResponse
{
    private function __construct(
        public array $items,
        public int $total
    ) {
    }

    /**
     * @param Enumerable<int, Chat> $entities
     */
    public static function build(Enumerable $entities): self
    {
        $chatsWithMessages = $entities->filter(fn (Chat $chat): bool => $chat->getFirstMessage() instanceof Message);

        $grouped = $chatsWithMessages
            ->groupBy(function (Chat $chat): string {
                /** @var Message $firstMessage */
                $firstMessage = $chat->getFirstMessage();
                return $firstMessage->date->format('d.m.Y');
            })
            ->map(function (Enumerable $chatsByDay, string $date): array {
                return [
                    'date'       => $date,
                    'chatsCount' => $chatsByDay->count(),
                    'chats'      => $chatsByDay->map(function (Chat $chat): array {
                        return $chat->toArray();
                    })->values()->toArray(),
                ];
            })->values()->toArray();

        return new self(
            $grouped,
            $entities->getTotal()
        );
    }
}
