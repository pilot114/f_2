<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Enum\StatusType;
use DateTimeImmutable;

class Chat
{
    private bool $hasAnswer;

    public function __construct(
        public readonly int $id,
        private ?StatusType $status,
        private Client $client,
        /** @var Message[] $messages */
        private array $messages = [],
        private bool $isMarkedChat = false
    ) {
    }

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
    }

    public function setHasAnswer(bool $hasAnswer): void
    {
        $this->hasAnswer = $hasAnswer;
    }

    public function isHasAnswer(): bool
    {
        return $this->hasAnswer;
    }

    public function isMarkedChat(): bool
    {
        return $this->isMarkedChat;
    }

    public function setIsMarkedChat(bool $isMarkedChat): void
    {
        $this->isMarkedChat = $isMarkedChat;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getFirstMessage(): ?Message
    {
        $messages = $this->messages;

        return array_shift($messages);
    }

    public function toArray(): array
    {
        $messages = $this->messages;
        /** @var Message $firstMessage */
        $firstMessage = array_shift($messages);

        return [
            'id'           => $this->id,
            'status'       => $this->status?->value,
            'messageId'    => $firstMessage->id,
            'clientName'   => $this->client->name,
            'firstMessage' => $firstMessage->text,
            'date'         => $firstMessage->date->format(DateTimeImmutable::ATOM),
            'isMarkedChat' => $this->isMarkedChat,
            'hasAnswer'    => $this->hasAnswer,
            'messages'     => array_map(fn (Message $message): array => $message->toArray(), $messages),
        ];
    }
}
