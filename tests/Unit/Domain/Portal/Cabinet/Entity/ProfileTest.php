<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Address;
use App\Domain\Portal\Cabinet\Entity\Contacts;
use App\Domain\Portal\Cabinet\Entity\Department;
use App\Domain\Portal\Cabinet\Entity\Position;
use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Entity\WorkTime;
use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use DateTimeImmutable;

it('creates profile with basic fields', function (): void {
    $position = new Position(name: 'Developer', description: 'Software Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address(city: 'Moscow');

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    expect($profile->getId())->toBe(1)
        ->and($profile->getUserId())->toBe(100);
});

it('returns null avatar images when no avatar', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        avatar: null,
    );

    $avatarImages = $profile->getAvatarImages();

    expect($avatarImages['small'])->toBeNull()
        ->and($avatarImages['large'])->toBeNull();
});

it('sets and gets birthday', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $birthday = new DateTimeImmutable('1990-05-15');
    $profile->setBirthday($birthday);

    expect($profile->getBirthday())->toBe($birthday);
});

it('gets telegram from contacts', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com', telegram: '@johndoe');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    expect($profile->getTelegram())->toBe('@johndoe');
});

it('gets phone from contacts', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com', phone: '+79991234567');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    expect($profile->getPhone())->toBe('+79991234567');
});

it('gets city from address', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address(city: 'Saint Petersburg');

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    expect($profile->getCity())->toBe('Saint Petersburg');
});

it('sets telegram', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $profile->setTelegram('@newhandle');

    expect($profile->getTelegram())->toBe('@newhandle');
});

it('sets phone', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $profile->setPhone('+79991234567');

    expect($profile->getPhone())->toBe('+79991234567');
});

it('sets city', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $profile->setCity('Novosibirsk');

    expect($profile->getCity())->toBe('Novosibirsk');
});

it('sets and gets pass card', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $profile->setPassCard('PASS123');

    expect($profile->getPassCard())->toBe('PASS123');
});

it('gets snils', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        snils: '123-456-789 00',
    );

    expect($profile->getSnils())->toBe('123-456-789 00');
});

it('converts to array', function (): void {
    $position = new Position(name: 'Developer', description: 'Software Developer');
    $contacts = new Contacts(email: 'test@example.com', telegram: '@johndoe', phone: '+79991234567');
    $address = new Address(city: 'Moscow');

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $result = $profile->toArray();

    expect($result['userId'])->toBe(100)
        ->and($result['name'])->toBe('John Doe')
        ->and($result['contacts']['email'])->toBe('test@example.com')
        ->and($result['contacts']['telegram'])->toBe('@johndoe')
        ->and($result['contacts']['phone'])->toBe('+79991234567')
        ->and($result['address']['city'])->toBe('Moscow')
        ->and($result['position']['name'])->toBe('Developer');
});

it('includes birthday in array when set', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();
    $birthday = new DateTimeImmutable('1990-05-15');

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        birthday: $birthday,
    );

    $result = $profile->toArray();

    expect($result['birthday'])->toContain('1990-05-15');
});

it('returns empty departments hierarchy when no departments', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    expect($profile->getDepartmentsHierarchy())->toBeEmpty();
});

it('builds department hierarchy correctly', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $topDepartment = new Department(id: 10, name: 'Engineering', parentId: 1);
    $childDepartment = new Department(id: 20, name: 'Backend Team', parentId: 10);

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        departments: [$topDepartment, $childDepartment],
    );

    $hierarchy = $profile->getDepartmentsHierarchy();

    expect($hierarchy['id'])->toBe(10)
        ->and($hierarchy['name'])->toBe('Engineering')
        ->and($hierarchy['child'])->toBeArray()
        ->and($hierarchy['child']['id'])->toBe(20)
        ->and($hierarchy['child']['name'])->toBe('Backend Team');
});

it('sets and gets work time', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $workTime = new WorkTime(
        id: 1,
        userId: 100,
        start: new DateTimeImmutable('09:00'),
        end: new DateTimeImmutable('18:00'),
        timeZone: WorkTimeTimeZone::MOSCOW
    );

    $profile->setWorkTime($workTime);

    expect($profile->getWorkTime())->toBe($workTime);
});

it('sets and gets hide birthday', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        hideBirthday: false,
    );

    $profile->setHideBirthday(true);

    expect($profile->getHideBirthday())->toBeTrue();
});

it('includes null workTime in array when not set', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
    );

    $result = $profile->toArray();

    expect($result['workTime'])->toBeNull();
});

it('includes workTime in array when set', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $workTime = new WorkTime(
        id: 1,
        userId: 100,
        start: new DateTimeImmutable('09:00'),
        end: new DateTimeImmutable('18:00'),
        timeZone: WorkTimeTimeZone::MOSCOW
    );

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        workTime: $workTime,
    );

    $result = $profile->toArray();

    expect($result['workTime'])->toBeArray()
        ->and($result['workTime']['start'])->toBe((new DateTimeImmutable('09:00'))->format(DateTimeImmutable::ATOM))
        ->and($result['workTime']['end'])->toBe((new DateTimeImmutable('18:00'))->format(DateTimeImmutable::ATOM))
        ->and($result['workTime']['timeZone'])->toBe(WorkTimeTimeZone::MOSCOW);
});

it('includes pass card and snils in array', function (): void {
    $position = new Position(name: 'Developer');
    $contacts = new Contacts(email: 'test@example.com');
    $address = new Address();

    $profile = new Profile(
        id: 1,
        userId: 100,
        name: 'John Doe',
        position: $position,
        contacts: $contacts,
        address: $address,
        snils: '123-456-789 00',
    );

    $profile->setPassCard('PASS123');

    $result = $profile->toArray();

    expect($result['passCard'])->toBe('PASS123')
        ->and($result['snils'])->toBe('123-456-789 00');
});
