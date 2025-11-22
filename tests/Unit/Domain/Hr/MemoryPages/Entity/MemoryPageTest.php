<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\Entity;

use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;
use DomainException;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('creates MemoryPage with required parameters', function (): void {
    // Arrange
    $employee = new Employee(1, 'Test User', []);
    $birthDate = new DateTimeImmutable('1990-01-01');
    $deathDate = new DateTimeImmutable('2024-12-31');
    $createDate = new DateTimeImmutable('2025-01-01');

    // Act
    $page = new MemoryPage(
        id: 1,
        employee: $employee,
        birthDate: $birthDate,
        deathDate: $deathDate,
        createDate: $createDate,
        obituary: 'Short obituary',
        obituaryFull: 'Full obituary text',
        comments: [],
        workPeriods: []
    );

    // Assert
    expect($page->getId())->toBe(1)
        ->and($page->getEmployee())->toBe($employee)
        ->and($page->getBirthDate())->toBe($birthDate)
        ->and($page->getDeathDate())->toBe($deathDate)
        ->and($page->getCreateDate())->toBe($createDate)
        ->and($page->getObituary())->toBe('Short obituary')
        ->and($page->getObituaryFull())->toBe('Full obituary text');
});

it('sets and gets employee', function (): void {
    // Arrange
    $page = createMemoryPage();
    $newEmployee = new Employee(2, 'New Employee', []);

    // Act
    $page->setEmployee($newEmployee);

    // Assert
    expect($page->getEmployee())->toBe($newEmployee);
});

it('sets and gets dates', function (): void {
    // Arrange
    $page = createMemoryPage();
    $newBirthDate = new DateTimeImmutable('1985-05-15');
    $newDeathDate = new DateTimeImmutable('2025-06-20');
    $newCreateDate = new DateTimeImmutable('2025-07-01');

    // Act
    $page->setBirthDate($newBirthDate);
    $page->setDeathDate($newDeathDate);
    $page->setCreateDate($newCreateDate);

    // Assert
    expect($page->getBirthDate())->toBe($newBirthDate)
        ->and($page->getDeathDate())->toBe($newDeathDate)
        ->and($page->getCreateDate())->toBe($newCreateDate);
});

it('sets and gets obituary texts', function (): void {
    // Arrange
    $page = createMemoryPage();

    // Act
    $page->setObituary('New short obituary');
    $page->setObituaryFull('New full obituary text');

    // Assert
    expect($page->getObituary())->toBe('New short obituary')
        ->and($page->getObituaryFull())->toBe('New full obituary text');
});

it('throws exception when getting last response without work periods', function (): void {
    // Arrange
    $page = createMemoryPage();

    // Act & Assert
    expect(fn (): Response => $page->getLastResponse())
        ->toThrow(DomainException::class, 'Нет периодов работы для данного сотрудника.');
});

it('returns last response based on end date', function (): void {
    // Arrange
    $page = createMemoryPage();
    $response1 = new Response(1, 'Old Dept');
    $response2 = new Response(2, 'New Dept');

    $wp1 = new WorkPeriod(
        id: 1,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2010-01-01'),
        endDate: new DateTimeImmutable('2015-12-31'),
        response: $response1
    );

    $wp2 = new WorkPeriod(
        id: 2,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2016-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response2
    );

    $page->addWorkPeriod($wp1);
    $page->addWorkPeriod($wp2);

    // Act
    $result = $page->getLastResponse();

    // Assert
    expect($result)->toBe($response2);
});

it('sets main photo', function (): void {
    // Arrange
    $page = createMemoryPage();
    $photo = Mockery::mock(File::class);

    // Act
    $page->setMainPhoto($photo);

    // Assert
    expect($page->getMainPhoto())->toBe($photo);
});

it('adds other photos', function (): void {
    // Arrange
    $page = createMemoryPage();
    $photo1 = Mockery::mock(File::class);
    $photo2 = Mockery::mock(File::class);

    // Act
    $page->addOtherPhoto($photo1);
    $page->addOtherPhoto($photo2);

    // Assert
    expect($page->getOtherPhotos())->toHaveCount(2)
        ->and($page->getOtherPhotos())->toContain($photo1, $photo2);
});

it('gets other photo by id', function (): void {
    // Arrange
    $page = createMemoryPage();
    $photo = Mockery::mock(File::class);
    $photo->shouldReceive('getId')->andReturn(10);
    $page->addOtherPhoto($photo);

    // Act
    $result = $page->getOtherPhotoById(10);

    // Assert
    expect($result)->toBe($photo);
});

it('returns null when other photo not found', function (): void {
    // Arrange
    $page = createMemoryPage();

    // Act
    $result = $page->getOtherPhotoById(999);

    // Assert
    expect($result)->toBeNull();
});

it('removes photo from other photos', function (): void {
    // Arrange
    $page = createMemoryPage();
    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getId')->andReturn(10);
    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getId')->andReturn(20);

    $page->addOtherPhoto($photo1);
    $page->addOtherPhoto($photo2);

    // Act
    $page->removePhotoFromOtherPhotos(10);

    // Assert
    expect($page->getOtherPhotos())->toHaveCount(1)
        ->and($page->getOtherPhotoById(10))->toBeNull()
        ->and($page->getOtherPhotoById(20))->toBe($photo2);
});

it('adds and gets work periods', function (): void {
    // Arrange
    $page = createMemoryPage();
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: new Response(1, 'Test')
    );

    // Act
    $page->addWorkPeriod($wp);

    // Assert
    expect($page->getWorkPeriods())->toHaveCount(1)
        ->and($page->getWorkPeriods())->toContain($wp);
});

it('gets work period by id', function (): void {
    // Arrange
    $page = createMemoryPage();
    $wp = new WorkPeriod(
        id: 5,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: new Response(1, 'Test')
    );
    $page->addWorkPeriod($wp);

    // Act
    $result = $page->getWorkPeriodById(5);

    // Assert
    expect($result)->toBe($wp);
});

it('returns null when work period not found', function (): void {
    // Arrange
    $page = createMemoryPage();

    // Act
    $result = $page->getWorkPeriodById(999);

    // Assert
    expect($result)->toBeNull();
});

it('removes work period', function (): void {
    // Arrange
    $page = createMemoryPage();
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: new Response(1, 'Test')
    );
    $page->addWorkPeriod($wp);

    // Act
    $result = $page->removeWorkPeriod(1);

    // Assert
    expect($result)->toBeTrue()
        ->and($page->getWorkPeriods())->toBeEmpty();
});

it('returns false when removing non-existent work period', function (): void {
    // Arrange
    $page = createMemoryPage();

    // Act
    $result = $page->removeWorkPeriod(999);

    // Assert
    expect($result)->toBeFalse();
});

it('sorts comments by pinned status and date', function (): void {
    // Arrange
    $page = createMemoryPage(comments: [
        new Comment(
            id: 1,
            memoryPageId: 1,
            isPinned: false,
            createDate: new DateTimeImmutable('2025-01-03'),
            employee: new Employee(1, 'User1', []),
            text: 'Comment 1'
        ),
        new Comment(
            id: 2,
            memoryPageId: 1,
            isPinned: true,
            createDate: new DateTimeImmutable('2025-01-02'),
            employee: new Employee(1, 'User2', []),
            text: 'Comment 2'
        ),
        new Comment(
            id: 3,
            memoryPageId: 1,
            isPinned: false,
            createDate: new DateTimeImmutable('2025-01-01'),
            employee: new Employee(1, 'User3', []),
            text: 'Comment 3'
        ),
    ]);

    // Act
    $page->sortComments();
    $comments = $page->getComments();

    // Assert - pinned first, then by date
    expect($comments[0]->getId())->toBe(2) // pinned
        ->and($comments[1]->getId())->toBe(3) // oldest non-pinned
        ->and($comments[2]->getId())->toBe(1); // newest non-pinned
});

it('sorts work periods by end date', function (): void {
    // Arrange
    $page = createMemoryPage();

    $wp1 = new WorkPeriod(
        id: 1,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2015-01-01'),
        endDate: new DateTimeImmutable('2020-12-31'),
        response: new Response(1, 'Dept1')
    );

    $wp2 = new WorkPeriod(
        id: 2,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2010-01-01'),
        endDate: new DateTimeImmutable('2015-12-31'),
        response: new Response(1, 'Dept2')
    );

    $page->addWorkPeriod($wp1);
    $page->addWorkPeriod($wp2);

    // Act
    $page->sortWorkPeriods();
    $periods = $page->getWorkPeriods();

    // Assert
    expect($periods[0]->getId())->toBe(2) // earliest end date first
        ->and($periods[1]->getId())->toBe(1);
});

it('sets up photos for different collections', function (): void {
    // Arrange
    $page = createMemoryPage();
    $comment = new Comment(
        id: 10,
        memoryPageId: 1,
        isPinned: false,
        createDate: new DateTimeImmutable('2025-01-01'),
        employee: new Employee(5, 'Test User', []),
        text: 'Test comment'
    );
    $page = createMemoryPage(comments: [$comment]);

    $mainPhoto = Mockery::mock(File::class);
    $mainPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION);

    $otherPhoto1 = Mockery::mock(File::class);
    $otherPhoto1->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);

    $otherPhoto2 = Mockery::mock(File::class);
    $otherPhoto2->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);

    $commentPhoto = Mockery::mock(File::class);
    $commentPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION);
    $commentPhoto->shouldReceive('getIdInCollection')->andReturn(10);

    $avatarPhoto = Mockery::mock(File::class);
    $avatarPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::USER_AVATAR_COLLECTION);
    $avatarPhoto->shouldReceive('getIdInCollection')->andReturn(5);

    $photos = collect([$mainPhoto, $otherPhoto1, $otherPhoto2, $commentPhoto, $avatarPhoto]);

    // Act
    $page->setUpPhotos($photos);

    // Assert
    expect($page->getMainPhoto())->toBe($mainPhoto)
        ->and($page->getOtherPhotos())->toHaveCount(2)
        ->and($page->getOtherPhotos())->toContain($otherPhoto1, $otherPhoto2);
});

it('converts memory page to array correctly', function (): void {
    // Arrange
    $response = new Response(1, 'Test Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 1,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );

    $page = createMemoryPage(workPeriods: [$wp]);

    $mainPhoto = Mockery::mock(File::class);
    $mainPhoto->shouldReceive('getId')->andReturn(5);
    $mainPhoto->shouldReceive('getImageUrls')->andReturn([
        'small' => 'url1',
        'large' => 'url2',
    ]);
    $page->setMainPhoto($mainPhoto);

    $otherPhoto = Mockery::mock(File::class);
    $otherPhoto->shouldReceive('getId')->andReturn(6);
    $otherPhoto->shouldReceive('getImageUrls')->andReturn([
        'small' => 'url3',
        'large' => 'url4',
    ]);
    $page->addOtherPhoto($otherPhoto);

    // Act
    $array = $page->toArray();

    // Assert
    expect($array)->toHaveKeys(['id', 'employee', 'birthDate', 'deathDate', 'createDate', 'obituary', 'obituaryFull', 'mainPhoto', 'otherPhotos', 'response', 'workPeriods', 'comments'])
        ->and($array['id'])->toBe(1)
        ->and($array['obituary'])->toBe('Test obituary')
        ->and($array['obituaryFull'])->toBe('Test full obituary')
        ->and($array['mainPhoto']['id'])->toBe(5)
        ->and($array['otherPhotos'])->toHaveCount(1)
        ->and($array['otherPhotos'][0]['id'])->toBe(6)
        ->and($array['workPeriods'])->toHaveCount(1)
        ->and($array['comments'])->toBeArray();
});

// Helper function
function createMemoryPage(array $comments = [], array $workPeriods = []): MemoryPage
{
    return new MemoryPage(
        id: 1,
        employee: new Employee(1, 'Test User', []),
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Test obituary',
        obituaryFull: 'Test full obituary',
        comments: $comments,
        workPeriods: $workPeriods
    );
}
