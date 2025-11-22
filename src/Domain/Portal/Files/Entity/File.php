<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Entity;

use App\Common\Service\File\FileService;
use App\Domain\Portal\Files\Dto\FileResponse;
use App\Domain\Portal\Files\Enum\AllowedImageType;
use App\Domain\Portal\Files\Enum\ImageSize;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;
use DateTimeInterface;

#[Entity(name: 'test.cp_files', sequenceName: 'test.sq_cp_files')]
class File
{
    public const USERPIC_COLLECTION = 'userpic';

    public function __construct(
        #[Column] private int                                   $id,
        #[Column] private string                                $name,
        #[Column(name: 'fpath')] private string                 $filePath,
        #[Column(name: 'idemp')] private int                    $userId,
        #[Column(name: 'parentid')] private int                 $idInCollection,
        #[Column(name: 'parent_tbl')] private string            $collectionName,
        #[Column(name: 'ext')] private string                   $extension,
        #[Column(name: 'date_edit')] private DateTimeImmutable  $lastEditedDate = new DateTimeImmutable(),
        #[Column(name: 'is_on_static')] private int             $isOnStatic = 1,
    ) {
    }

    public function updateFileData(string $filePath, string $name, string $extension): void
    {
        $this->name = $name;
        $this->filePath = $filePath;
        $this->extension = $extension;
        $this->lastEditedDate = new DateTimeImmutable();
    }

    public function isUserpic(): bool
    {
        return $this->collectionName === self::USERPIC_COLLECTION;
    }

    /** @return array{original: string, small: string, medium: string, large: string} */
    public function getImageUrls(): array
    {
        $expire = $this->getLastEditedDate();

        return [
            'original' => FileService::getCpFileUrlView($this->id, $expire),
            'small'    => FileService::getCpFileUrlView($this->id, $expire, ImageSize::SMALL),
            'medium'   => FileService::getCpFileUrlView($this->id, $expire, ImageSize::MEDIUM),
            'large'    => FileService::getCpFileUrlView($this->id, $expire, ImageSize::BIG),
        ];
    }

    public function isImage(): bool
    {
        return AllowedImageType::tryFrom(mb_strtolower($this->extension)) instanceof AllowedImageType;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getNameForDownload(): string
    {
        if (str_ends_with(mb_strtolower($this->name), mb_strtolower('.' . $this->extension))) {
            return $this->name;
        }

        return sprintf('%s.%s', $this->name, $this->extension);
    }

    public function getLastEditedDate(): DateTimeImmutable
    {
        return $this->lastEditedDate;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    public function getIdInCollection(): int
    {
        return $this->idInCollection;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toFileResponse(): FileResponse
    {
        return new FileResponse(
            id: $this->id,
            name: $this->name,
            extension: $this->extension,
            downloadUrl: FileService::getCpFileUrlDownload($this->id),
            viewUrl: FileService::getCpFileUrlView($this->id),
            collectionName: $this->collectionName,
            idInCollection: $this->idInCollection,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'extension'      => $this->extension,
            'userId'         => $this->userId,
            'lastEditedDate' => $this->lastEditedDate->format(DateTimeInterface::ATOM),
            'downloadUrl'    => FileService::getCpFileUrlDownload($this->id),
            'viewUrl'        => FileService::getCpFileUrlView($this->id),
            'collection'     => [
                'idInCollection' => $this->idInCollection,
                'name'           => $this->collectionName,
            ],
        ];
    }
}
