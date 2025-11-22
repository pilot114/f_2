<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Entity;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_MP_PERSONAL_PAGE')]
class MemoryPageListItem
{
    private ?File $mainPhoto = null;

    public function __construct(
        #[Column] public readonly int      $id,
        #[Column] public readonly Employee $employee,
        #[Column(name: 'main_photo_id')] public int $mainPhotoId,
        #[Column] public string  $obituary,
        #[Column(name: 'response')] public readonly Response $response,
        #[Column(name: 'comments_count')] public int  $commentsCount = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->employee->name,
            'mainPhoto' => [
                'id'   => $this->mainPhotoId,
                'urls' => $this->mainPhoto?->getImageUrls(),
            ],
            'obituary'      => $this->obituary,
            'response'      => $this->response->name,
            'commentsCount' => $this->commentsCount,
        ];
    }

    public function getMainPhoto(): ?File
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?File $mainPhoto): void
    {
        $this->mainPhoto = $mainPhoto;
    }
}
