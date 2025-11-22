<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\Domain\Events\Rewards\DTO\GroupResponse;
use App\Domain\Events\Rewards\Entity\Country;
use App\System\DomainSourceCodeFinder;
use App\System\RPC\Attribute\JsonSchemaExtractor;
use App\System\RPC\Attribute\RpcMethodLoader;
use Mockery;
use Symfony\Contracts\Cache\CacheInterface;

class RpcMethodLoaderMock extends RpcMethodLoader
{
    public function callBuildSchemaByDto(string $typeName): array
    {
        return $this->extractor->buildSchemaByDto($typeName);
    }
}

class TestDto
{
    public function __construct(
        /** @var array<int, array{old: array<int, array<int, string>>, new: Country[], detId: int, detName: string[], detCode: array{id: int, name: string}}> */
        public array $array2,
        /** @var string[] */
        public array $array1,
        /** @var array<int, array{detId: int, detName: string}> */
        public array $array3,
        /** @var array{id: int, name: string} */
        public array $array4,
        /** @var Country[] */
        public array $array5,
        /** @var string[] */
        public array $array6,
        /** @var array<int, Country> */
        public array $countries2,
        public int $total,
        public GroupResponse $response
    ) {
    }
}

it('test build schema By dto', function (): void {
    $loader = new RpcMethodLoaderMock(
        Mockery::mock(DomainSourceCodeFinder::class),
        Mockery::mock(CacheInterface::class),
        'test',
        new JsonSchemaExtractor()
    );

    $schema = $loader->callBuildSchemaByDto(TestDto::class);

    $this->assertArrayHasKey('type', $schema);
    $this->assertEquals('object', $schema['type']);
    $this->assertArrayHasKey('properties', $schema);
});
