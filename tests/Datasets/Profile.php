<?php

declare(strict_types=1);

namespace App\Tests\Datasets;

use App\Domain\Portal\Cabinet\Entity\Address;
use App\Domain\Portal\Cabinet\Entity\Congratulation;
use App\Domain\Portal\Cabinet\Entity\Contacts;
use App\Domain\Portal\Cabinet\Entity\Department;
use App\Domain\Portal\Cabinet\Entity\Position;
use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Entity\WorkTime;
use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;

function makeProfile(): Profile
{
    $profile = new Profile(
        id: 9999,
        userId: 9999,
        name: 'Иванов Иван',
        position: new Position('менеджер', 'описание обязанностей'),
        contacts: new Contacts('asd@mail.ru', 'telegram', '+79898989898'),
        address: new Address('Москва'),
        avatar: makeAvatar(9999),
        departments: [
            123 => new Department(123, 'департаменты', Department::TOP_LEVEL_DEPARTMENT_ID),
            456 => new Department(456, 'департаменты', 123),
            789 => new Department(789, 'департаменты', 456),
        ],
        birthday: new DateTimeImmutable('01.01.1988'),
        snils: '123123',
        workTime: new WorkTime(
            1,
            9999,
            new DateTimeImmutable('2025-05-02 08:30'),
            new DateTimeImmutable('2025-05-02 18:30'),
            WorkTimeTimeZone::MOSCOW
        )
    );

    $profile->setPassCard('123123123');

    return $profile;
}

function makeAvatar(int $userId): File
{
    return new File(
        id: $userId,
        name: 'file.jpg',
        filePath: 'path/to/static/file.jpg',
        userId: $userId,
        idInCollection: 1234,
        collectionName: 'userpic',
        extension: 'jpg',
        lastEditedDate: new DateTimeImmutable('01.01.2024'),
    );
}

function genAvatarRawData(File $avatar): array
{
    return [
        [$avatar->toArray()],
    ];
}

function makeCongratulation(): Congratulation
{
    return new Congratulation(
        id: 9999,
        fromUserId: 5555,
        fromUserName: 'Иванов Иван',
        message: 'поздравляю',
        year: new DateTimeImmutable('11.01.2024'),
        avatar: 'path/to/static/file.jpg',
    );
}

function generateProfileRawData(Profile $profile): array
{
    return [$profile->toArray()];
}

dataset('profile', [makeProfile()]);
dataset('congratulations', [makeCongratulation()]);
dataset('profile db data', [generateProfileRawData(makeProfile())]);
dataset('avatar', [makeAvatar(9999)]);
dataset('avatar db data', [genAvatarRawData(makeAvatar(9999))]);
