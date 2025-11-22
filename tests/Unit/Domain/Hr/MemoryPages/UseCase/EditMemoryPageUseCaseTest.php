<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\EditMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\DTO\WorkPeriod as WorkPeriodDto;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use App\Domain\Hr\MemoryPages\UseCase\EditMemoryPageUseCase;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use DomainException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

afterEach(function (): void {
    Mockery::close();
});

it('edits memory page successfully', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Original obituary',
        obituaryFull: 'Original full obituary',
        comments: [],
        workPeriods: [$wp]
    );

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: 'Updated obituary',
        obituaryFull: 'Updated full obituary',
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')
        ->with(Mockery::on(fn ($req): bool => $req instanceof GetMemoryPageRequest && $req->id === 10))
        ->once()
        ->andReturn($memoryPage);

    $photoRepository->shouldReceive('getAllForMemoryPage')
        ->with($memoryPage)
        ->once()
        ->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();

    $memoryPageCommandRepo->shouldReceive('update')
        ->with($memoryPage)
        ->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getObituary())->toBe('Updated obituary')
        ->and($result->getObituaryFull())->toBe('Updated full obituary');
});

it('throws exception when too many other photos', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    // Add 10 existing photos
    for ($i = 1; $i <= 10; $i++) {
        $photo = Mockery::mock(File::class);
        $photo->shouldReceive('getId')->andReturn($i);
        $memoryPage->addOtherPhoto($photo);
    }

    // Try to add one more
    $photosToAdd = [
        new Photo(id: null, base64: 'newphoto', toDelete: false),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToAdd,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(DomainException::class, 'не должно быть больше 10 дополнительных фото');
});

it('throws exception when no work periods left', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage->addWorkPeriod($wp);

    // Try to delete the only work period
    $workPeriodsToDelete = [
        new WorkPeriodDto(
            id: 1,
            startDate: null,
            endDate: null,
            responseId: null,
            achievements: null,
            toDelete: true
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $workPeriodsToDelete
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(DomainException::class, 'должен быть хотя бы один период работы');
});

it('changes main photo', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $oldMainPhoto = Mockery::mock(File::class);
    $oldMainPhoto->shouldReceive('getId')->andReturn(5);
    $oldMainPhoto->shouldReceive('getUserId')->andReturn(1);
    $oldMainPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::MAIN_IMAGE_COLLECTION);
    $memoryPage->setMainPhoto($oldMainPhoto);

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: 'newmainphoto',
        otherPhotos: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([$oldMainPhoto]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $tempFile = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('newmainphoto')
        ->once()
        ->andReturn($tempFile);

    $photoService->shouldReceive('commonDelete')
        ->with(5, 1)
        ->once();

    $newMainPhoto = Mockery::mock(File::class);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempFile,
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
            null,
            10
        )
        ->once()
        ->andReturn($newMainPhoto);

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getMainPhoto())->toBe($newMainPhoto);
});

it('adds new other photo', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $photosToAdd = [
        new Photo(id: null, base64: 'newphoto', toDelete: false),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToAdd,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $tempFile = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('newphoto')
        ->once()
        ->andReturn($tempFile);

    $newPhoto = Mockery::mock(File::class);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempFile,
            MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
            null,
            10
        )
        ->once()
        ->andReturn($newPhoto);

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getOtherPhotos())->toContain($newPhoto);
});

it('deletes other photo', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );
    $existingPhoto = Mockery::mock(File::class);
    $existingPhoto->shouldReceive('getId')->andReturn(5);
    $existingPhoto->shouldReceive('getUserId')->andReturn(1);
    $existingPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $memoryPage->addOtherPhoto($existingPhoto);

    $photosToDelete = [
        new Photo(id: 5, base64: null, toDelete: true),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToDelete,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([$existingPhoto]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $photoService->shouldReceive('commonDelete')
        ->with(5, 1)
        ->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    //    expect($result->getOtherPhotoById(5))->toBeNull();
});

it('throws exception when photo to delete not found', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage->addWorkPeriod($wp);

    $photosToDelete = [
        new Photo(id: 999, base64: null, toDelete: true),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToDelete,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $memoryPageCommandRepo->shouldReceive('update')->never();

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(NotFoundHttpException::class, 'фотография с id = 999 не найдена в коллекции дополнительных фото');
});

it('updates existing other photo', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $existingPhoto = Mockery::mock(File::class);
    $existingPhoto->shouldReceive('getId')->andReturn(5);
    $existingPhoto->shouldReceive('getUserId')->andReturn(1);
    $existingPhoto->shouldReceive('getCollectionName')->andReturn(MemoryPagePhotoService::OTHER_IMAGE_COLLECTION);
    $memoryPage->addOtherPhoto($existingPhoto);

    $photosToUpdate = [
        new Photo(id: 5, base64: 'updatedphoto', toDelete: false),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToUpdate,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([$existingPhoto]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $tempFile = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $imageBase64->shouldReceive('baseToFile')
        ->with('updatedphoto')
        ->once()
        ->andReturn($tempFile);

    $photoService->shouldReceive('commonDelete')
        ->with(5, 1)
        ->once();

    $newPhoto = Mockery::mock(File::class);
    $photoService->shouldReceive('commonUpload')
        ->with(
            $tempFile,
            MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
            null,
            10
        )
        ->once()
        ->andReturn($newPhoto);

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getOtherPhotos())->toContain($newPhoto);
});

it('adds new work period', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $existingWp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2015-01-01'),
        endDate: new DateTimeImmutable('2020-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$existingWp]
    );

    $newWorkPeriod = [
        new WorkPeriodDto(
            id: null,
            startDate: new DateTimeImmutable('2021-01-01'),
            endDate: new DateTimeImmutable('2024-12-31'),
            responseId: 1,
            achievements: 'Achievement 1',
            toDelete: false
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $newWorkPeriod
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $responsesQueryRepo->shouldReceive('findOrFail')
        ->with(1, 'нe найдена должность')
        ->once()
        ->andReturn($response);

    $workPeriodsCommandRepo->shouldReceive('create')
        ->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getWorkPeriods())->toHaveCount(2);
});

it('updates existing work period', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $updatedWorkPeriod = [
        new WorkPeriodDto(
            id: 1,
            startDate: new DateTimeImmutable('2019-01-01'),
            endDate: new DateTimeImmutable('2023-12-31'),
            responseId: 1,
            achievements: 'Updated achievements',
            toDelete: false
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $updatedWorkPeriod
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $responsesQueryRepo->shouldReceive('findOrFail')
        ->with(1, 'нe найдена должность')
        ->once()
        ->andReturn($response);

    $workPeriodsCommandRepo->shouldReceive('update')
        ->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getWorkPeriods())->toHaveCount(1);
});

it('deletes work period', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp1 = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2015-01-01'),
        endDate: new DateTimeImmutable('2020-12-31'),
        response: $response
    );
    $wp2 = new WorkPeriod(
        id: 2,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2021-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp1, $wp2]
    );

    $workPeriodsToDelete = [
        new WorkPeriodDto(
            id: 1,
            startDate: null,
            endDate: null,
            responseId: null,
            achievements: null,
            toDelete: true
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $workPeriodsToDelete
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    $workPeriodsCommandRepo->shouldReceive('delete')
        ->with(1)
        ->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getWorkPeriods())->toHaveCount(1);
});

it('updates employee when provided', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $newEmployee = new Employee(id: 2, name: 'New User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: 2,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $employeeQueryRepo->shouldReceive('findOrFail')
        ->with(2, 'на найден сотрудник')
        ->once()
        ->andReturn($newEmployee);

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getEmployee())->toBe($newEmployee);
});

it('updates dates when provided', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $newBirthDate = new DateTimeImmutable('1985-05-15');
    $newDeathDate = new DateTimeImmutable('2025-06-20');

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: $newBirthDate,
        deathDate: $newDeathDate,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();
    $memoryPageCommandRepo->shouldReceive('update')->once();

    // Act
    $result = $useCase->edit($request);

    // Assert
    expect($result->getBirthDate())->toBe($newBirthDate)
        ->and($result->getDeathDate())->toBe($newDeathDate);
});

it('throws exception when work period to delete not found', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp1 = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $wp2 = new WorkPeriod(
        id: 2,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2015-01-01'),
        endDate: new DateTimeImmutable('2019-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp1, $wp2]
    );

    $workPeriodsToDelete = [
        new WorkPeriodDto(
            id: 999,
            startDate: null,
            endDate: null,
            responseId: null,
            achievements: null,
            toDelete: true
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $workPeriodsToDelete
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $workPeriodsCommandRepo->shouldReceive('delete')->with(999)->once();

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(NotFoundHttpException::class, 'в коллекции рабочих периодов нет рабочего периода с id = 999');
});

it('throws exception when work period to update not found', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $workPeriodsToUpdate = [
        new WorkPeriodDto(
            id: 999,
            startDate: new DateTimeImmutable('2020-01-01'),
            endDate: new DateTimeImmutable('2024-12-31'),
            responseId: 1,
            achievements: 'achievements',
            toDelete: false
        ),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: [],
        workPeriods: $workPeriodsToUpdate
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $responsesQueryRepo->shouldReceive('findOrFail')->andReturn($response);

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(NotFoundHttpException::class, 'в коллекции рабочих периодов нет рабочего периода с id = 999');
});

it('throws exception when photo to update not found', function (): void {
    // Arrange
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $employeeQueryRepo = Mockery::mock(EmployeeQueryRepository::class);
    $responsesQueryRepo = Mockery::mock(ResponsesQueryRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);
    $imageBase64 = Mockery::mock(ImageBase64::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoRepository = Mockery::mock(MemoryPagePhotoQueryRepository::class);

    $useCase = new EditMemoryPageUseCase(
        $memoryPageCommandRepo,
        $employeeQueryRepo,
        $responsesQueryRepo,
        $workPeriodsCommandRepo,
        $memoryPageQueryRepo,
        $transaction,
        $imageBase64,
        $photoService,
        $photoRepository
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $response = new Response(id: 1, name: 'Dept');
    $wp = new WorkPeriod(
        id: 1,
        memoryPageId: 10,
        startDate: new DateTimeImmutable('2020-01-01'),
        endDate: new DateTimeImmutable('2024-12-31'),
        response: $response
    );
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Obituary',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: [$wp]
    );

    $photosToUpdate = [
        new Photo(id: 999, base64: 'updatedphoto', toDelete: false),
    ];

    $request = new EditMemoryPageRequest(
        id: 10,
        employeeId: null,
        birthDate: null,
        deathDate: null,
        obituary: null,
        obituaryFull: null,
        mainPhotoBase64: null,
        otherPhotos: $photosToUpdate,
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoRepository->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    $transaction->shouldReceive('beginTransaction')->once();
    $imageBase64->shouldReceive('baseToFile')->andReturn(Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class));

    // Act & Assert
    expect(fn (): MemoryPage => $useCase->edit($request))
        ->toThrow(NotFoundHttpException::class, 'фотография с id = 999 не найдена в коллекции дополнительных фото');
});
