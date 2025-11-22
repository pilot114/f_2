<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\File;

use App\Common\Service\File\FileService;
use App\Common\Service\File\TempFileRegistry;
use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Files\Enum\ImageSize;
use App\Domain\Portal\Files\Repository\FileCommandRepository;
use App\Domain\Portal\Files\Repository\FileQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

beforeEach(function (): void {
    $this->httpClientMock = Mockery::mock(HttpClientInterface::class);

    $this->client = new StaticClient(
        'http://static.example.com',
        'secret',
        $this->httpClientMock,
        Mockery::mock(SecurityUser::class),
        Mockery::mock(TempFileRegistry::class),
    );

    $this->currentUser = Mockery::mock(SecurityUser::class);
    $this->readRepo = Mockery::mock(FileQueryRepository::class);
    $this->writeRepo = Mockery::mock(FileCommandRepository::class);

    $this->service = new FileService(
        $this->client,
        $this->currentUser,
        $this->readRepo,
        $this->writeRepo
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('gets static url', function (): void {
    // Arrange
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 123, 1, 'test_collection', 'jpg');

    // Act
    $url = $this->service->getStaticUrl($file);

    // Assert
    expect($url)->toBe('http://static.example.com/path/to/file.jpg');
});

it('gets by id', function (): void {
    // Arrange
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 123, 1, 'test_collection', 'jpg');
    $this->readRepo->shouldReceive('findOneBy')
        ->with([
            'id'           => 123,
            'is_on_static' => 1,
        ])
        ->andReturn($file);

    // Act
    $result = $this->service->getById(123);

    // Assert
    expect($result)->toBe($file);
});

it('gets file list by user id', function (): void {
    // Arrange
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 456, 1, 'test_collection', 'jpg');
    $this->readRepo->allows('findBy')
        ->with([
            'idemp'        => 456,
            'is_on_static' => 1,
        ])
        ->andReturns(new Collection([$file]));

    // Act
    $result = $this->service->getFileListByUserId(456);

    // Assert
    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->and($result->first())
        ->toBe($file);
});

it('correctly creates file entity on upload', function (string $originalName, ?string $guessedExtension, string $expectedBaseName, string $expectedExtension, ?string $mimeType = 'application/octet-stream'): void {
    // Arrange

    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('convertToSafeName')->with($originalName)->andReturn($originalName);
    $mockStaticClient->shouldReceive('uploadFile')
        ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
        ->andReturn('/path/to/' . $originalName);

    // 1. Создаём РЕАЛЬНЫЙ объект SecurityUser. Это гарантирует, что все его свойства будут инициализированы.
    $testUser = createSecurityUser(123);

    // 2. Создаём экземпляр FileService, передавая ему настоящего пользователя.
    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser, // Используем реальный объект
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(UploadedFile::class);
    $file->allows('getClientOriginalName')->andReturns($originalName);
    $file->allows('guessExtension')->andReturns($guessedExtension);
    $file->allows('getMimeType')->andReturns($mimeType);

    $this->readRepo->allows('getNextIdByCollectionName')->with('test_collection')->andReturns(1);
    $this->readRepo->allows('fileExists')->andReturns(false);

    $createdFile = null;
    $this->writeRepo->allows('create')
        ->with(Mockery::on(function (File $file) use (&$createdFile): true {
            $createdFile = $file;
            return true;
        }))
        ->andReturnUsing(function () use (&$createdFile): ?File {
            return $createdFile;
        });

    // Act
    $result = $serviceForThisTest->commonUpload($file, 'test_collection');

    // Assert
    $resultArray = $result->toArray();
    expect($result)->toBeInstanceOf(File::class)
        ->and($resultArray['name'])->toBe($expectedBaseName)
        ->and($resultArray['extension'])->toBe($expectedExtension);
})->with([
    'simple file'                           => ['test.jpg', 'jpg', 'test', 'jpg', 'image/jpeg'],
    'file with multiple dots'               => ['archive.v1.2.zip', 'zip', 'archive.v1.2', 'zip', 'application/zip'],
    'file with no extension'                => ['README', null, 'README', 'unknown', 'application/unknown'],
    'file with uppercase extension'         => ['document.DOCX', 'docx', 'document', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'file with dot at the beginning'        => ['.env', null, '', 'env', 'text/plain'],
    'file with differing guessed extension' => ['photo.jpeg', 'jpg', 'photo', 'jpeg', 'image/jpeg'],
]);

it('gets cp file url download', function (): void {
    $url = FileService::getCpFileUrlDownload(123);

    expect($url)->toBe('/api/v2/file/123/download');
});

it('gets cp file url view without parameters', function (): void {
    $url = FileService::getCpFileUrlView(456);

    expect($url)->toBe('/api/v2/file/456/view');
});

it('gets cp file url view with mini size', function (): void {
    $url = FileService::getCpFileUrlView(123, size: ImageSize::MINI);

    expect($url)->toBe('/api/v2/file/123/view/fit_60');
});

it('gets cp file url view with small size', function (): void {
    $url = FileService::getCpFileUrlView(123, size: ImageSize::SMALL);

    expect($url)->toBe('/api/v2/file/123/view/fit_80');
});

it('gets cp file url view with medium size', function (): void {
    $url = FileService::getCpFileUrlView(123, size: ImageSize::MEDIUM);

    expect($url)->toBe('/api/v2/file/123/view/fit_400');
});

it('gets cp file url view with big size', function (): void {
    $url = FileService::getCpFileUrlView(123, size: ImageSize::BIG);

    expect($url)->toBe('/api/v2/file/123/view/fit_800');
});

it('gets cp file url view with expire date', function (): void {
    $date = new DateTimeImmutable('2024-01-01 00:00:00');
    $url = FileService::getCpFileUrlView(123, expireDate: $date);

    expect($url)->toContain('/api/v2/file/123/view?t=')
        ->and($url)->toContain((string) $date->getTimestamp());
});

it('gets cp file url view with size and expire date', function (): void {
    $date = new DateTimeImmutable('2024-01-01 00:00:00');
    $url = FileService::getCpFileUrlView(123, expireDate: $date, size: ImageSize::MEDIUM);

    expect($url)->toBe('/api/v2/file/123/view/fit_400?t=' . $date->getTimestamp());
});

it('gets file by id with user id', function (): void {
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 123, 1, 'test_collection', 'jpg');

    $this->readRepo->shouldReceive('findOneBy')
        ->with([
            'id'           => 123,
            'idemp'        => 456,
            'is_on_static' => 1,
        ])
        ->andReturn($file);

    $result = $this->service->getById(123, 456);

    expect($result)->toBe($file);
});

it('gets file list by user id with collection name', function (): void {
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 456, 1, 'documents', 'jpg');

    $this->readRepo->shouldReceive('findBy')
        ->with([
            'idemp'        => 456,
            'parent_tbl'   => 'documents',
            'is_on_static' => 1,
        ])
        ->andReturn(new Collection([$file]));

    $result = $this->service->getFileListByUserId(456, 'documents');

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->and($result->first())
        ->toBe($file);
});

it('deletes file successfully', function (): void {
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 456, 1, 'test_collection', 'jpg');

    $mockClient = Mockery::mock(StaticClient::class);
    $mockClient->shouldReceive('removeFile')
        ->with('/path/to/file.jpg')
        ->andReturn(true);

    $serviceForTest = new FileService(
        $mockClient,
        $this->currentUser,
        $this->readRepo,
        $this->writeRepo
    );

    $this->readRepo->shouldReceive('findOneBy')
        ->with([
            'id'           => 123,
            'idemp'        => 456,
            'is_on_static' => 1,
        ])
        ->andReturn($file);

    $this->writeRepo->shouldReceive('delete')
        ->with(123)
        ->andReturn(true);

    $result = $serviceForTest->commonDelete(123, 456);

    expect($result)->toBeTrue();
});

it('returns false when deleting non-existent file', function (): void {
    $this->readRepo->shouldReceive('findOneBy')
        ->with([
            'id'           => 999,
            'idemp'        => 456,
            'is_on_static' => 1,
        ])
        ->andReturn(null);

    $result = $this->service->commonDelete(999, 456);

    expect($result)->toBeFalse();
});

it('returns false when static client fails to remove file', function (): void {
    $file = new File(1, 'test.jpg', '/path/to/file.jpg', 456, 1, 'test_collection', 'jpg');

    $mockClient = Mockery::mock(StaticClient::class);
    $mockClient->shouldReceive('removeFile')
        ->with('/path/to/file.jpg')
        ->andReturn(false);

    $serviceForTest = new FileService(
        $mockClient,
        $this->currentUser,
        $this->readRepo,
        $this->writeRepo
    );

    $this->readRepo->shouldReceive('findOneBy')
        ->with([
            'id'           => 123,
            'idemp'        => 456,
            'is_on_static' => 1,
        ])
        ->andReturn($file);

    $result = $serviceForTest->commonDelete(123, 456);

    expect($result)->toBeFalse();
});

it('uploads file with free path when duplicate name exists', function (): void {
    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('convertToSafeName')
        ->with('test.jpg')
        ->andReturn('test.jpg');
    $mockStaticClient->shouldReceive('convertToSafeName')
        ->with('test_1.jpg')
        ->andReturn('test_1.jpg');
    $mockStaticClient->shouldReceive('uploadFile')
        ->andReturn('/path/to/test_1.jpg');

    $testUser = createSecurityUser(123);

    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser,
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(UploadedFile::class);
    $file->allows('getClientOriginalName')->andReturns('test.jpg');
    $file->allows('getMimeType')->andReturns('image/jpeg');

    // First check - file exists
    // Second check - new name is free
    $this->readRepo->shouldReceive('fileExists')
        ->with('/public/test_collection/test.jpg')
        ->andReturn(true);
    $this->readRepo->shouldReceive('fileExists')
        ->with('/public/test_collection/test_1.jpg')
        ->andReturn(false);
    $this->readRepo->allows('getNextIdByCollectionName')->with('test_collection')->andReturns(1);

    $createdFile = null;
    $this->writeRepo->allows('create')
        ->with(Mockery::on(function (File $file) use (&$createdFile): true {
            $createdFile = $file;
            return true;
        }))
        ->andReturnUsing(function () use (&$createdFile): ?File {
            return $createdFile;
        });

    $result = $serviceForThisTest->commonUpload($file, 'test_collection');

    expect($result)->toBeInstanceOf(File::class);
});

it('uploads userpic with user id as filename', function (): void {
    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('uploadFile')
        ->with(Mockery::any(), '123.jpg', '/cp_userpic/', false)
        ->andReturn('/path/to/123.jpg');

    $testUser = createSecurityUser(123);

    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser,
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(UploadedFile::class);
    $file->allows('getClientOriginalName')->andReturns('avatar.jpg');
    $file->allows('getMimeType')->andReturns('image/jpeg');

    $createdFile = null;
    $this->writeRepo->allows('create')
        ->with(Mockery::on(function (File $file) use (&$createdFile): true {
            $createdFile = $file;
            return true;
        }))
        ->andReturnUsing(function () use (&$createdFile): ?File {
            return $createdFile;
        });

    $result = $serviceForThisTest->commonUpload($file, 'userpic', null, 123);

    expect($result)->toBeInstanceOf(File::class);
});

it('updates existing file on upload', function (): void {
    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('convertToSafeName')->andReturn('updated.jpg');
    $mockStaticClient->shouldReceive('uploadFile')
        ->andReturn('/path/to/updated.jpg');

    $testUser = createSecurityUser(123);

    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser,
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(UploadedFile::class);
    $file->allows('getClientOriginalName')->andReturns('updated.jpg');
    $file->allows('getMimeType')->andReturns('image/jpeg');

    $existingFile = new File(1, 'old.jpg', '/path/to/old.jpg', 123, 1, 'test_collection', 'jpg');

    $this->readRepo->allows('fileExists')->andReturns(false);

    $this->writeRepo->shouldReceive('update')
        ->with($existingFile)
        ->andReturn($existingFile);

    $result = $serviceForThisTest->commonUpload($file, 'test_collection', $existingFile);

    expect($result)->toBe($existingFile);
});

it('uploads file with rewrite flag', function (): void {
    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('convertToSafeName')->andReturn('test.jpg');
    $mockStaticClient->shouldReceive('uploadFile')
        ->with(Mockery::any(), Mockery::any(), Mockery::any(), true)
        ->andReturn('/path/to/test.jpg');

    $testUser = createSecurityUser(123);

    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser,
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(UploadedFile::class);
    $file->allows('getClientOriginalName')->andReturns('test.jpg');
    $file->allows('getMimeType')->andReturns('image/jpeg');

    $this->readRepo->allows('getNextIdByCollectionName')->andReturns(1);
    $this->readRepo->allows('fileExists')->andReturns(false);

    $createdFile = null;
    $this->writeRepo->allows('create')
        ->with(Mockery::on(function (File $file) use (&$createdFile): true {
            $createdFile = $file;
            return true;
        }))
        ->andReturnUsing(function () use (&$createdFile): ?File {
            return $createdFile;
        });

    $result = $serviceForThisTest->commonUpload($file, 'test_collection', null, null, true);

    expect($result)->toBeInstanceOf(File::class);
});

it('uploads non-UploadedFile with basename', function (): void {
    $mockStaticClient = Mockery::mock(StaticClient::class);
    $mockStaticClient->shouldReceive('convertToSafeName')->andReturn('document.pdf');
    $mockStaticClient->shouldReceive('uploadFile')
        ->andReturn('/path/to/document.pdf');

    $testUser = createSecurityUser(123);

    $serviceForThisTest = new FileService(
        $mockStaticClient,
        $testUser,
        $this->readRepo,
        $this->writeRepo
    );

    $file = Mockery::mock(\Symfony\Component\HttpFoundation\File\File::class);
    $file->allows('getBasename')->andReturns('document.pdf');
    $file->allows('getMimeType')->andReturns('application/pdf');

    $this->readRepo->allows('getNextIdByCollectionName')->andReturns(1);
    $this->readRepo->allows('fileExists')->andReturns(false);

    $createdFile = null;
    $this->writeRepo->allows('create')
        ->with(Mockery::on(function (File $file) use (&$createdFile): true {
            $createdFile = $file;
            return true;
        }))
        ->andReturnUsing(function () use (&$createdFile): ?File {
            return $createdFile;
        });

    $result = $serviceForThisTest->commonUpload($file, 'documents');

    expect($result)->toBeInstanceOf(File::class);
});
