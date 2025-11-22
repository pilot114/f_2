<?php

declare(strict_types=1);

namespace App\Tests\Datasets;

use App\System\DomainSourceCodeFinder;
use App\System\RPC\Attribute\JsonSchemaExtractor;
use App\System\RPC\Attribute\RpcMethodLoader;
use Generator;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Finder\Finder;

function queryEndpointsWithExamples(): Generator
{
    $loader = new RpcMethodLoader(
        fileLoader: new DomainSourceCodeFinder(new Finder(), __DIR__ . '/../../'),
        cache: new NullAdapter(),
        env: 'test',
        extractor: new JsonSchemaExtractor()
    );
    foreach ($loader->load() as $rpcMethod) {
        if (!$rpcMethod->isQuery()) {
            continue;
        }
        foreach ($rpcMethod->examples as $example) {
            $caseName = $rpcMethod->name;
            if (!empty($example['summary'])) {
                $caseName .= ' ' . $example['summary'];
            }

            yield $caseName => [
                $rpcMethod->name,
                $example['params'] ?? [],
            ];
        }
    }
}

dataset('queryEndpointsWithExamples', queryEndpointsWithExamples(...));
