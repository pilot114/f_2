<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Enumerable;

#[Entity(name: MemoryPage::TABLE, sequenceName: MemoryPage::SEQUENCE)]
class MemoryPage
{
    public const SEQUENCE = 'TEST.CP_MP_PERSONAL_PAGES_SQ';
    public const TABLE = 'TEST.CP_MP_PERSONAL_PAGES';

    private File $mainPhoto;
    /** @var File[] */
    private array $otherPhotos = [];

    public function __construct(
        #[Column] private int      $id,
        #[Column(name: 'cp_emp_id')] private Employee $employee,
        #[Column(name: 'birth_date')] private DateTimeImmutable             $birthDate,
        #[Column(name: 'death_date')] private DateTimeImmutable             $deathDate,
        #[Column(name: 'create_date')] private DateTimeImmutable            $createDate,
        #[Column] private string                                                     $obituary,
        #[Column(name: 'obituary_full')] private string                              $obituaryFull,
        /** @var Comment[] */
        #[Column(collectionOf: Comment::class)] private array                       $comments = [],
        /** @var WorkPeriod[] */
        #[Column(name: 'workperiods', collectionOf: WorkPeriod::class)] public array $workPeriods = [],
    ) {
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }

    public function getBirthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeImmutable $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getDeathDate(): DateTimeImmutable
    {
        return $this->deathDate;
    }

    public function setDeathDate(DateTimeImmutable $deathDate): void
    {
        $this->deathDate = $deathDate;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTimeImmutable $createDate): void
    {
        $this->createDate = $createDate;
    }

    public function getLastResponse(): Response
    {
        if ($this->workPeriods === []) {
            throw new DomainException('Нет периодов работы для данного сотрудника.');
        }

        $workPeriods = $this->workPeriods;
        usort($workPeriods, fn (WorkPeriod $a, WorkPeriod $b): int => $b->getEndDate() <=> $a->getEndDate());

        return $workPeriods[0]->getResponse();
    }

    /** @return Comment[] */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function setMainPhoto(File $file): void
    {
        $this->mainPhoto = $file;
    }

    public function addOtherPhoto(File $file): void
    {
        $this->otherPhotos[] = $file;
    }

    public function getObituary(): string
    {
        return $this->obituary;
    }

    public function getObituaryFull(): string
    {
        return $this->obituaryFull;
    }

    /** @param Enumerable<int, File> $photos */
    public function setUpPhotos(Enumerable $photos): void
    {
        foreach ($photos as $photo) {
            if ($photo->getCollectionName() === MemoryPagePhotoService::MAIN_IMAGE_COLLECTION) {
                $this->setMainPhoto($photo);
            }
            if ($photo->getCollectionName() === MemoryPagePhotoService::OTHER_IMAGE_COLLECTION) {
                $this->addOtherPhoto($photo);
            }
            if ($photo->getCollectionName() === MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION) {
                foreach ($this->getComments() as $comment) {
                    if ($comment->getId() === $photo->getIdInCollection()) {
                        $comment->addPhoto($photo);
                    }
                }
            }
            if ($photo->getCollectionName() === MemoryPagePhotoService::USER_AVATAR_COLLECTION) {
                foreach ($this->getComments() as $comment) {
                    if ($comment->getEmployee()->id === $photo->getIdInCollection()) {
                        $comment->getEmployee()->setAvatar($photo);
                    }
                }
            }

        }
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'employee'     => $this->employee->toArray(),
            'birthDate'    => $this->birthDate->format(DateTimeImmutable::ATOM),
            'deathDate'    => $this->deathDate->format(DateTimeImmutable::ATOM),
            'createDate'   => $this->createDate->format(DateTimeImmutable::ATOM),
            'obituary'     => $this->obituary,
            'obituaryFull' => $this->obituaryFull,
            'mainPhoto'    => [
                'id'   => $this->mainPhoto->getId(),
                'urls' => $this->mainPhoto->getImageUrls(),
            ],
            'otherPhotos' => array_map(fn (File $file): array => [
                'id'   => $file->getId(),
                'urls' => $file->getImageUrls(),
            ], $this->otherPhotos),
            'response'    => $this->getLastResponse()->toArray(),
            'workPeriods' => array_map(fn (WorkPeriod $workPeriod): array => $workPeriod->toArray(), array_values($this->workPeriods)),
            'comments'    => array_map(fn (Comment $comment): array => $comment->toArray(),  array_values($this->comments)),
        ];
    }

    public function addWorkPeriod(WorkPeriod $workPeriod): void
    {
        $this->workPeriods[] = $workPeriod;
    }

    public function setObituary(string $obituary): void
    {
        $this->obituary = $obituary;
    }

    public function setObituaryFull(string $obituaryFull): void
    {
        $this->obituaryFull = $obituaryFull;
    }

    public function getMainPhoto(): File
    {
        return $this->mainPhoto;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOtherPhotoById(int $photoId): ?File
    {
        foreach ($this->otherPhotos as $photo) {
            if ($photoId === $photo->getId()) {
                return $photo;
            }
        }
        return null;
    }

    public function getOtherPhotos(): array
    {
        return $this->otherPhotos;
    }

    public function removePhotoFromOtherPhotos(int $photoId): void
    {
        foreach ($this->otherPhotos as $key => $photo) {
            if ($photoId === $photo->getId()) {
                unset($this->otherPhotos[$key]);
                return;
            }
        }
    }

    public function getWorkPeriodById(int $workPeriodId): ?WorkPeriod
    {
        foreach ($this->workPeriods as $workPeriod) {
            if ($workPeriod->getId() === $workPeriodId) {
                return $workPeriod;
            }
        }
        return null;
    }

    public function getWorkPeriods(): array
    {
        return $this->workPeriods;
    }

    public function removeWorkPeriod(int $workPeriodId): bool
    {
        foreach ($this->workPeriods as $key => $workPeriod) {
            if ($workPeriod->getId() === $workPeriodId) {
                unset($this->workPeriods[$key]);
                return true;
            }
        }
        return false;
    }

    public function sortComments(): void
    {
        usort($this->comments, function (Comment $a, Comment $b): int {
            if ($a->isPinned() === $b->isPinned()) {
                return $a->getCreateDate() <=> $b->getCreateDate();
            }
            return $a->isPinned() ? -1 : 1;
        });
    }

    public function sortWorkPeriods(): void
    {
        usort($this->workPeriods, function (WorkPeriod $a, WorkPeriod $b): int {
            return $a->getEndDate() <=> $b->getEndDate();
        });
    }
}
