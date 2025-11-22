<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\CreateMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\DTO\WorkPeriod as WorkPeriodDto;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use App\Domain\Hr\MemoryPages\UseCase\CreateMemoryPageUseCase;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use Mockery;
use ReflectionClass;

afterEach(function (): void {
    Mockery::close();
});

it('creates memory page successfully', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new CreateMemoryPageUseCase(
        $memoryPageRepo,
        $employeeRepo,
        $responsesRepo,
        $workPeriodsRepo,
        $transaction,
        $imageBase64,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'IT Department');

    $workPeriod = new WorkPeriodDto(
        id: null,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        responseId: 1,
        achievements: 'Great achievements',
        toDelete: false
    );

    $request = new CreateMemoryPageRequest(
        employeeId: 1,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        obituary: 'Short obituary',
        obituaryFull: 'Full obituary text',
        mainPhotoBase64: 'mainphotobase64',
        otherPhotos: [],
        workPeriods: [$workPeriod]
    );

    $employeeRepo->shouldReceive('findOrFail')
        ->with(1, 'не найден сотрудник')
        ->once()
        ->andReturn($employee);

    $responsesRepo->shouldReceive('findOrFail')
        ->with(1, 'нe найдена должность')
        ->once()
        ->andReturn($response);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();

    $memoryPageRepo->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (MemoryPage $page): MemoryPage {
            $reflection = new ReflectionClass($page);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($page, 100);
            return $page;
        });

    $workPeriodsRepo->shouldReceive('create')->once();

    $tempMainPhoto = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('mainphotobase64')
        ->once()
        ->andReturn($tempMainPhoto);

    $mainPhoto = Mockery::mock(File::class);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempMainPhoto,
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
            null,
            100
        )
        ->once()
        ->andReturn($mainPhoto);

    // Act
    $result = $useCase->create($request);

    // Assert
    expect($result)->toBeInstanceOf(MemoryPage::class)
        ->and($result->getEmployee())->toBe($employee)
        ->and($result->getObituary())->toBe('Short obituary')
        ->and($result->getObituaryFull())->toBe('Full obituary text');
});

it('creates memory page with multiple work periods', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new CreateMemoryPageUseCase(
        $memoryPageRepo,
        $employeeRepo,
        $responsesRepo,
        $workPeriodsRepo,
        $transaction,
        $imageBase64,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response1 = new Response(id: 1, name: 'Department 1');
    $response2 = new Response(id: 2, name: 'Department 2');

    $workPeriod1 = new WorkPeriodDto(
        id: null,
        startDate: new DateTimeImmutable('2010-01-01'),
        endDate: new DateTimeImmutable('2015-12-31'),
        responseId: 1,
        achievements: 'Early achievements',
        toDelete: false
    );

    $workPeriod2 = new WorkPeriodDto(
        id: null,
        startDate: new DateTimeImmutable('2016-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        responseId: 2,
        achievements: 'Later achievements',
        toDelete: false
    );

    $request = new CreateMemoryPageRequest(
        employeeId: 1,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        obituary: 'Short',
        obituaryFull: 'Full',
        mainPhotoBase64: 'photo',
        otherPhotos: [],
        workPeriods: [$workPeriod1, $workPeriod2]
    );

    $employeeRepo->shouldReceive('findOrFail')->andReturn($employee);
    $responsesRepo->shouldReceive('findOrFail')
        ->with(1, 'нe найдена должность')
        ->once()
        ->andReturn($response1);
    $responsesRepo->shouldReceive('findOrFail')
        ->with(2, 'нe найдена должность')
        ->once()
        ->andReturn($response2);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();

    $memoryPageRepo->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (MemoryPage $page): MemoryPage {
            $reflection = new ReflectionClass($page);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($page, 100);
            return $page;
        });

    $workPeriodsRepo->shouldReceive('create')->twice();
    $imageBase64->shouldReceive('baseToFile')->andReturn(Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class));
    $photoService->shouldReceive('commonUpload')->andReturn(Mockery::mock(File::class));

    // Act
    $result = $useCase->create($request);

    // Assert
    expect($result->getWorkPeriods())->toHaveCount(2);
});

it('creates memory page with other photos', function (): void {
    // Arrange
    $memoryPageRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new CreateMemoryPageUseCase(
        $memoryPageRepo,
        $employeeRepo,
        $responsesRepo,
        $workPeriodsRepo,
        $transaction,
        $imageBase64,
        $photoService
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Department');

    $workPeriod = new WorkPeriodDto(
        id: null,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        responseId: 1,
        achievements: 'Achievements',
        toDelete: false
    );

    $request = new CreateMemoryPageRequest(
        employeeId: 1,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        obituary: 'Short',
        obituaryFull: 'Full',
        mainPhotoBase64: 'mainphoto',
        otherPhotos: [new Photo(base64: 'photo1'), new Photo(base64: 'photo2')],
        workPeriods: [$workPeriod]
    );

    $employeeRepo->shouldReceive('findOrFail')->andReturn($employee);
    $responsesRepo->shouldReceive('findOrFail')->andReturn($response);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $pageUnderTest = null;

    $memoryPageRepo->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (MemoryPage $page) use (&$pageUnderTest): MemoryPage {
            $reflection = new ReflectionClass($page);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($page, 100);
            $pageUnderTest = $page;
            return $page;
        });

    $workPeriodsRepo->shouldReceive('create')->once();

    $tempMainPhoto = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $tempPhoto1 = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $tempPhoto2 = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);

    $imageBase64->shouldReceive('baseToFile')
        ->with('mainphoto')
        ->once()
        ->andReturn($tempMainPhoto);
    $imageBase64->shouldReceive('baseToFile')
        ->with('photo1')
        ->once()
        ->andReturn($tempPhoto1);
    $imageBase64->shouldReceive('baseToFile')
        ->with('photo2')
        ->once()
        ->andReturn($tempPhoto2);

    $mainPhoto = Mockery::mock(File::class);
    $mainPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION);
    $otherPhoto1 = Mockery::mock(File::class);
    $otherPhoto1->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $otherPhoto2 = Mockery::mock(File::class);
    $otherPhoto2->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);

    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempMainPhoto,
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
            null,
            100
        )
        ->once()
        ->andReturn($mainPhoto);

    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempPhoto1,
            MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
            null,
            100
        )
        ->once()
        ->andReturn($otherPhoto1);

    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempPhoto2,
            MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
            null,
            100
        )
        ->once()
        ->andReturn($otherPhoto2);

    // Act
    $useCase->create($request);

    // Assert
    expect($pageUnderTest->getOtherPhotos())->toHaveCount(2)
        ->and($pageUnderTest->getOtherPhotos())->toContain($otherPhoto1, $otherPhoto2);
});
