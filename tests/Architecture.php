<?php

declare(strict_types=1);

// Архитектурные тесты для проверки структуры проекта

arch('не использовать системный код в бизнес-логике')
    ->expect('App\System')
    ->not->toBeUsedIn(['App\Domain', 'App\Common'])
;

arch('Gateways это интерфейсы')
    ->expect('App\Gateway')
    ->toBeInterfaces()
;

arch('Проверка зависимостей Entity')
    ->expect('App\Domain\*\*\Entity')
    ->toOnlyUse([
        'Database\ORM\Attribute',
        'Illuminate\Support\Enumerable',

        'App\Common\Exception',
        'App\Common\Helper',
        'App\Domain\*\*\Enum',
        'App\Domain\*\*\DTO',

        // TODO: исключения, подумать как быть с ними
        'App\Common\Service\Integration\StaticClient',
        'App\Common\Service\File\FileService',
        'App\Domain\Hr\MemoryPages\MemoryPagePhotoService',
        'Symfony\Component\Security\Core\User\UserInterface',
    ])
;

arch('Проверка зависимостей Repository')
    ->expect('App\Domain\*\*\Repository')
    ->toOnlyUse([
        'Database\ORM\CommandRepository',
        'Database\ORM\QueryRepository',
        'Database\ORM\Identity',
        'Database\Connection\ParamType',
        'Database\Connection\ParamMode',
        'Database\EntityNotFoundDatabaseException',
        'Illuminate\Support\Enumerable',

        'App\Common\Exception',
        'App\Common\Helper',
        'App\Domain\*\*\Enum',

        'App\Domain\*\*\Entity',
        'App\Domain\*\*\DTO',
        'App\Common\DTO',

        // TODO: исключения, подумать как быть с ними
        'App\Domain\Hr\MemoryPages\MemoryPagePhotoService',
        'Database\ORM\EntityTracker',
    ])
;

arch('Проверка зависимостей UseCase')
    ->expect('App\Domain\*\*\UseCase')
    ->toOnlyUse([
        'Database\Connection\TransactionInterface',
        'Database\ORM\Attribute\Loader',
        'Database\ORM\CommandRepositoryInterface',
        'Database\ORM\QueryRepositoryInterface',
        'Illuminate\Support\Enumerable',

        'App\Common\Exception',
        'App\Common\Helper',
        'App\Domain\*\*\Enum',

        'App\Domain\*\*\Entity',
        'App\Domain\*\*\DTO',
        'App\Common\DTO',

        'App\Common\Service',
        'App\Domain\*\*\Repository',
        'App\Domain\*\*\Service',

        'Psr\Cache\CacheItemInterface',
        'Psr\Cache\CacheItemPoolInterface',

        // TODO: исключения, подумать как быть с ними
        'App\Domain\Analytics\Mcp\Retriever',
        'Database\Schema\DbObject\Link',
        'Symfony\Component\HttpKernel\Exception',
        'OpenSpout\Reader\XLSX\Reader',
        'App\Domain\Hr\MemoryPages\MemoryPagePhotoService',
        'Database\ORM\EntityTracker',
        'App\Domain\Partners\SaleStructure\Exception\PartnerDomainException',
    ])
;

arch('Проверка зависимостей Controller')
    ->expect('App\Domain\*\*\Controller')
    ->toOnlyUse([
        'App\Common\Attribute',
        'PhpMcp\Server\Attributes\McpTool',
        'Illuminate\Support\Enumerable',
        'PhpMcp\Server\Attributes\Schema',

        'App\Domain\*\*\UseCase',
        'App\Domain\*\*\Enum',
        'App\Domain\*\*\Entity',
        'App\Domain\*\*\DTO',
        'App\Common\Helper',
        'App\Common\DTO',
        'App\Common\Service',

        'App\Domain\Portal\Security\Entity\SecurityUser',
        'Symfony\Component\Validator\Constraints',
        'Symfony\Component\Routing\Attribute\Route',
        'Symfony\Component\HttpKernel\Exception',
        'Symfony\Component\HttpFoundation\Request',
        'Symfony\Component\HttpFoundation\Response',
        'Symfony\Component\HttpFoundation\StreamedResponse',
        'Symfony\Component\HttpFoundation\JsonResponse',
        'Symfony\Component\HttpFoundation\ResponseHeaderBag',
        'Symfony\Component\HttpFoundation\File\UploadedFile',

        // TODO: исключения, подумать как быть с ними
        'App\Common\Service\Integration\JiraClient',
        'JiraRestApi\JiraException',
        'Symfony\Contracts\HttpClient\HttpClientInterface',
        'App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever',
        'App\Domain\Portal\Security\Repository\SecurityQueryRepository',
        'App\System\DomainSourceCodeFinder',
        'App\Common\Service\Excel\ExcelExportService',
        'Intervention\Image\Image',
        'Psr\Cache\CacheItemPoolInterface',
        'App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository',
    ])
;

arch('Проверка зависимостей DTO')
    ->expect('App\Domain\*\*\DTO')
    ->toOnlyUse([
        'Illuminate\Support\Enumerable',
        'App\Domain\*\*\Entity',
        'App\Domain\*\*\Enum',
        'App\Common\DTO',
        'App\Common\Helper',
        'App\Common\Attribute\RpcParam',
        'Symfony\Component\Validator\Constraints',
        'Symfony\Component\Validator\Context\ExecutionContextInterface',
    ])
;
