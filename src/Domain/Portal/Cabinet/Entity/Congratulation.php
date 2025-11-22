<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Enum\ImageSize;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'test.ut_birthday_msg')]
class Congratulation
{
    public function __construct(
        #[Column] private int $id,
        #[Column(name: 'from_user_id')] private int $fromUserId,
        #[Column(name: 'from_user_name')] private string $fromUserName,
        #[Column] private string $message,
        #[Column] private DateTimeImmutable $year,
        #[Column(name: 'fpath')] private ?string $avatar,
    ) {
    }

    /** @return array{small: ?string, medium: ?string} */
    public function getAvatar(): array
    {
        return [
            'small'  => $this->avatar ? StaticClient::getUserpicByUserId($this->fromUserId, ImageSize::SMALL) : null,
            'medium' => $this->avatar ? StaticClient::getUserpicByUserId($this->fromUserId, ImageSize::MEDIUM) : null,
        ];
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->getId(),
            'from_user_id'   => $this->getFromUserId(),
            'from_user_name' => $this->getFromUserName(),
            'message'        => $this->message,
            'year'           => $this->getYear()->format('Y'),
            'avatar'         => $this->getAvatar(),
        ];
    }

    public function getYear(): DateTimeImmutable
    {
        return $this->year;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFromUserId(): int
    {
        return $this->fromUserId;
    }

    public function getFromUserName(): string
    {
        return $this->fromUserName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
