<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'test.cp_emp', sequenceName: 'net.sq_cp_emp')]
class Profile
{
    private ?string $passCard = null;

    public function __construct(
        #[Column(name: 'id')] private int                        $id,
        #[Column(name: 'user_id')] private int                   $userId,
        #[Column(name: 'name')] private string                   $name,
        #[Column(name: 'position')] private Position             $position,
        #[Column(name: 'contacts')] private Contacts             $contacts,
        #[Column(name: 'address')] private Address               $address,
        #[Column(name: 'avatar')] private ?File                  $avatar = null,
        /** @var array<int, Department> $departments */
        #[Column(collectionOf: Department::class)] private array $departments = [],
        #[Column(name: 'birthday')] private ?DateTimeImmutable   $birthday = null,
        #[Column(name: 'hide_birthday')] private bool            $hideBirthday = false,
        #[Column(name: 'snils')] private ?string                 $snils = null,
        #[Column(name: 'worktime')] private ?WorkTime            $workTime = null,
    ) {
    }

    /** @return array{small: ?string, large: ?string} */
    public function getAvatarImages(): array
    {
        if (!$this->avatar instanceof File) {
            return [
                'small' => null,
                'large' => null,
            ];
        }
        return $this->avatar->getImageUrls();
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getDepartmentsHierarchy(): array
    {
        $this->buildDepartmentHierarchy();
        foreach ($this->departments as $department) {
            if ($department->isTopLevel()) {
                return $department->toArray();
            }
        }
        return [];
    }

    public function setBirthday(?DateTimeImmutable $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getBirthday(): ?DateTimeImmutable
    {
        return $this->birthday;
    }

    public function getTelegram(): ?string
    {
        return $this->contacts->getTelegram();
    }

    public function getPhone(): ?string
    {
        return $this->contacts->getPhone();
    }

    public function getCity(): ?string
    {
        return $this->address->getCityName();
    }

    public function setTelegram(?string $telegram): void
    {
        $this->contacts->setTelegram($telegram);
    }

    public function setPhone(?string $phone): void
    {
        $this->contacts->setPhone($phone);
    }

    public function setCity(?string $city): void
    {
        $this->address->setCity($city);
    }

    public function getPassCard(): ?string
    {
        return $this->passCard;
    }

    public function setPassCard(?string $passCard): void
    {
        $this->passCard = $passCard;
    }

    public function getSnils(): ?string
    {
        return $this->snils;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'userId'               => $this->userId,
            'name'                 => $this->name,
            'passCard'             => $this->passCard,
            'snils'                => $this->snils,
            'birthday'             => $this->getBirthday()?->format(DateTimeImmutable::ATOM),
            'hideBirthday'         => $this->hideBirthday,
            'contacts'             => $this->contacts->toArray(),
            'address'              => $this->address->toArray(),
            'avatar'               => $this->getAvatarImages(),
            'position'             => $this->position->toArray(),
            'departmentsHierarchy' => $this->getDepartmentsHierarchy(),
            'workTime'             => $this->workTime?->toArray(),
        ];
    }

    private function buildDepartmentHierarchy(): void
    {
        foreach ($this->departments as $currentDepartment) {
            foreach ($this->departments as $children) {
                if ($currentDepartment->getId() === $children->getParentId()) {
                    $currentDepartment->addChild($children);
                }
            }
        }
    }

    public function getWorkTime(): ?WorkTime
    {
        return $this->workTime;
    }

    public function setWorkTime(?WorkTime $workTime): void
    {
        $this->workTime = $workTime;
    }

    public function setHideBirthday(bool $hideBirthday): void
    {
        $this->hideBirthday = $hideBirthday;
    }

    public function getHideBirthday(): bool
    {
        return $this->hideBirthday;
    }
}
