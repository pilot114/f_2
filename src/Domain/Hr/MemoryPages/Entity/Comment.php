<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Entity;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: Comment::TABLE, sequenceName: Comment::SEQUENCE)]
class Comment
{
    public const SEQUENCE = 'TEST.CP_MP_COMMENTS_SQ';
    public const TABLE = 'TEST.CP_MP_COMMENTS';

    /** @var File[] */
    private array $photos = [];

    public function __construct(
        #[Column] private int                                             $id,
        #[Column(name: 'personal_page_id')] public readonly int           $memoryPageId,
        #[Column(name: 'is_pinned')] private bool                         $isPinned,
        #[Column(name: 'create_date')] private readonly DateTimeImmutable $createDate,
        #[Column(name: 'create_cp_emp_id')] private Employee              $employee,
        #[Column] private string                                          $text,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function addPhoto(File $file): void
    {
        $this->photos[] = $file;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getPhotoById(int $photoId): ?File
    {
        foreach ($this->photos as $photo) {
            if ($photoId === $photo->getId()) {
                return $photo;
            }
        }
        return null;
    }

    public function removePhotoPhotos(int $photoId): void
    {
        foreach ($this->photos as $key => $photo) {
            if ($photoId === $photo->getId()) {
                unset($this->photos[$key]);
                return;
            }
        }
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function setIsPinned(bool $isPinned): void
    {
        $this->isPinned = $isPinned;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'employee'   => $this->employee->toArray(),
            'text'       => $this->text,
            'isPinned'   => $this->isPinned,
            'createDate' => $this->createDate->format(DateTimeImmutable::ATOM),
            'photos'     => array_map(fn (File $photo): array => [
                'id'   => $photo->getId(),
                'urls' => $photo->getImageUrls(),
            ], array_values($this->photos)),
        ];
    }

}
