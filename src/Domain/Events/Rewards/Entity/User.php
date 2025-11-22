<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\UserResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_emp', sequenceName: 'not clear')]
class User
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
    ) {
    }

    public function toUserResponse(): UserResponse
    {
        return new UserResponse(
            id: $this->id,
            name: $this->name
        );
    }

    public function getShortName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        if (!$parts || $parts[0] === '') {
            return '';
        }
        $short = $parts[0];
        if (isset($parts[1]) && $parts[1] !== '') {
            $short .= ' ' . mb_substr($parts[1], 0, 1) . '.';
        }
        return $short;
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }

}
