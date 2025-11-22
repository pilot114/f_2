<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Dit\Reporter\Command;

use App\Domain\Dit\Reporter\Command\DebugReporter;
use App\Domain\Dit\Reporter\Entity\Report;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\CpConnection;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->conn = Mockery::mock(CpConnection::class);
    $this->repo = Mockery::mock(ReportQueryRepository::class);
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->executeReportUseCase = Mockery::mock(ExecuteReportUseCase::class);

    $this->secRepo->shouldReceive('findOrFail')
        ->with(4026, 'не найден сотрудник')
        ->andReturn(createSecurityUser());

    $this->command = new DebugReporter(
        $this->conn,
        $this->repo,
        $this->secRepo,
        $this->executeReportUseCase
    );
});

it('has correct name and description', function (): void {
    $reflection = new ReflectionClass(DebugReporter::class);
    $attributes = $reflection->getAttributes();

    $asCommandAttr = null;
    foreach ($attributes as $attribute) {
        if ($attribute->getName() === 'Symfony\Component\Console\Attribute\AsCommand') {
            $asCommandAttr = $attribute;
            break;
        }
    }

    expect($asCommandAttr)->not->toBeNull();
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(DebugReporter::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(4)
        ->and($parameters[0]->getName())->toBe('conn')
        ->and($parameters[1]->getName())->toBe('repo')
        ->and($parameters[2]->getName())->toBe('secRepo')
        ->and($parameters[3]->getName())->toBe('executeReportUseCase');
});

it('has configure method with options', function (): void {
    $reflection = new ReflectionClass(DebugReporter::class);

    expect($reflection->hasMethod('configure'))->toBeTrue();
});

it('has protected methods for processing', function (): void {
    $reflection = new ReflectionClass(DebugReporter::class);

    expect($reflection->hasMethod('debugReport'))->toBeTrue()
        ->and($reflection->hasMethod('printReport'))->toBeTrue()
        ->and($reflection->hasMethod('printReportStats'))->toBeTrue()
        ->and($reflection->hasMethod('measurePerformance'))->toBeTrue();
});

it('has formatBytes private method', function (): void {
    $reflection = new ReflectionClass(DebugReporter::class);

    expect($reflection->hasMethod('formatBytes'))->toBeTrue();
});

it('formats bytes correctly', function (int $bytes, string $expected): void {
    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('formatBytes');
    $method->setAccessible(true);

    $result = $method->invoke($this->command, $bytes);

    expect($result)->toBe($expected);
})->with([
    'bytes'                   => [512, '512 b'],
    'kilobytes'               => [2048, '2 kb'],
    'kilobytes with decimals' => [1536, '1.5 kb'],
    'megabytes'               => [2097152, '2 mb'],
    'megabytes with decimals' => [1572864, '1.5 mb'],
    'gigabytes'               => [2147483648, '2 gb'],
]);

it('measures performance correctly', function (): void {
    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('measurePerformance');
    $method->setAccessible(true);

    $called = false;
    $result = $method->invoke($this->command, function () use (&$called): array {
        $called = true;
        return [[
            'data' => 'test',
        ], 10];
    });

    expect($called)->toBeTrue()
        ->and($result)->toBeArray()
        ->and($result[0])->toBe([
            'data' => 'test',
        ])
        ->and($result[1])->toBe(10);

    // Check lastExecution was set
    $lastExecutionProp = $reflection->getProperty('lastExecution');
    $lastExecutionProp->setAccessible(true);
    $lastExecution = $lastExecutionProp->getValue($this->command);

    expect($lastExecution)->toBeArray()
        ->and($lastExecution)->toHaveKeys(['sec', 'memory']);
});

it('prints report when query data exists', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report',
        'owner' => [
            'name' => 'Test Owner',
        ],
        'data' => [
            'queries' => [
                [
                    'fields' => [
                        [
                            'fieldName'    => 'id',
                            'bandName'     => 'main',
                            'displayLabel' => 'ID',
                            'isCurrency'   => false,
                        ],
                        [
                            'fieldName'    => 'amount',
                            'bandName'     => 'main',
                            'displayLabel' => 'Amount',
                            'isCurrency'   => true,
                        ],
                    ],
                    'params' => [
                        [
                            'name'         => 'dateFrom',
                            'caption'      => 'Date From',
                            'dataType'     => 'date',
                            'defaultValue' => '2024-01-01',
                            'dictionaryId' => null,
                            'customValues' => null,
                            'required'     => true,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(123)
        ->andReturn($mockReport);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('printReport');
    $method->setAccessible(true);

    $result = $method->invoke($this->command, 123, $output);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('fields')
        ->and($result)->toHaveKey('params');

    $outputText = $output->fetch();
    expect($outputText)->toContain('Test Report')
        ->and($outputText)->toContain('Test Owner');
});

it('returns empty array when report has no query data', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Empty Report',
        'owner' => [
            'name' => 'Test Owner',
        ],
        'data' => [
            'queries' => [],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(999)
        ->andReturn($mockReport);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('printReport');
    $method->setAccessible(true);

    $result = $method->invoke($this->command, 999, $output);

    expect($result)->toBe([]);
});

it('prints report without owner name', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report No Owner',
        'owner' => [],
        'data'  => [
            'queries' => [],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(100)
        ->andReturn($mockReport);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('printReport');
    $method->setAccessible(true);

    $result = $method->invoke($this->command, 100, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('no owner name');
});

it('debugReport writes error when no data', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Empty Report',
        'owner' => [],
        'data'  => [
            'queries' => [],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(999)
        ->andReturn($mockReport);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('debugReport');
    $method->setAccessible(true);

    $method->invoke($this->command, 999, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('Нет данных');
});

it('debugReport executes report with parameters', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report',
        'owner' => [
            'name' => 'Owner',
        ],
        'data' => [
            'queries' => [
                [
                    'fields' => [[
                        'fieldName'    => 'id',
                        'bandName'     => 'main',
                        'displayLabel' => 'ID',
                        'isCurrency'   => false,
                    ]],
                    'params' => [
                        [
                            'name'         => 'param1',
                            'caption'      => 'Param 1',
                            'dataType'     => 'string',
                            'defaultValue' => 'test',
                            'dictionaryId' => null,
                            'customValues' => null,
                            'required'     => true,
                        ],
                        [
                            'name'         => 'param2',
                            'caption'      => 'Param 2',
                            'dataType'     => 'string',
                            'defaultValue' => 'optional',
                            'dictionaryId' => null,
                            'customValues' => null,
                            'required'     => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(123)
        ->andReturn($mockReport);

    $this->executeReportUseCase->shouldReceive('executeReport')
        ->with(123, Mockery::type('App\Domain\Portal\Security\Entity\SecurityUser'), [
            'param1' => 'test',
        ])
        ->andReturn([[
            'result' => 'data',
        ], 5]);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('debugReport');
    $method->setAccessible(true);

    $method->invoke($this->command, 123, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('Выполнение с параметрами')
        ->and($outputText)->toContain('Кол-во: 5');
});

it('debugReport handles execution errors', function (): void {
    $output = new BufferedOutput();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report',
        'owner' => [
            'name' => 'Owner',
        ],
        'data' => [
            'queries' => [
                [
                    'fields' => [[
                        'fieldName'    => 'id',
                        'bandName'     => 'main',
                        'displayLabel' => 'ID',
                        'isCurrency'   => false,
                    ]],
                    'params' => [],
                ],
            ],
        ],
    ]);

    $this->repo->shouldReceive('getReport')
        ->with(123)
        ->andReturn($mockReport);

    $this->executeReportUseCase->shouldReceive('executeReport')
        ->andThrow(new Exception('Test error'));

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('debugReport');
    $method->setAccessible(true);

    $method->invoke($this->command, 123, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('Ошибка')
        ->and($outputText)->toContain('Test error');
});

it('printReportStats shows statistics', function (): void {
    $output = new BufferedOutput();

    $reports = [
        [
            'id'          => 1,
            'report_type' => '2',
            'owner'       => 100,
        ],
        [
            'id'          => 2,
            'report_type' => '2',
            'owner'       => 100,
        ],
        [
            'id'          => 3,
            'report_type' => '2',
            'owner'       => 200,
        ],
        [
            'id'          => 4,
            'report_type' => '1',
            'owner'       => 100,
        ], // folder
    ];

    $generator = (function () {
        yield [
            'id'     => 100,
            'name'   => 'Owner 1',
            'active' => 'Y',
        ];
        yield [
            'id'     => 200,
            'name'   => 'Owner 2',
            'active' => 'N',
        ];
    })();

    $this->repo->shouldReceive('getReportOwners')
        ->with([100, 200])
        ->andReturn($generator);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('printReportStats');
    $method->setAccessible(true);

    $method->invoke($this->command, $reports, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('Отчётов: 3')
        ->and($outputText)->toContain('Owner 1')
        ->and($outputText)->toContain('Owner 2')
        ->and($outputText)->toContain('неактивен');
});

it('printReportStats handles empty owners', function (): void {
    $output = new BufferedOutput();

    $reports = [
        [
            'id'          => 1,
            'report_type' => '2',
            'owner'       => 100,
        ],
    ];

    $generator = (function () {
        yield [
            'id'     => 100,
            'name'   => 'Owner 1',
            'active' => 'Y',
        ];
        yield null; // Invalid owner
    })();

    $this->repo->shouldReceive('getReportOwners')
        ->with([100])
        ->andReturn($generator);

    $reflection = new ReflectionClass($this->command);
    $method = $reflection->getMethod('printReportStats');
    $method->setAccessible(true);

    $method->invoke($this->command, $reports, $output);

    $outputText = $output->fetch();
    expect($outputText)->toContain('Отчётов: 1');
});

it('execute runs for specific report id', function (): void {
    $input = Mockery::mock(InputInterface::class);
    $output = new BufferedOutput();

    $input->shouldReceive('getArgument')->with('id')->andReturn('123');
    $input->shouldReceive('getOption')->never();
    $input->shouldReceive('bind')->andReturn(null);
    $input->shouldReceive('isInteractive')->andReturn(false);
    $input->shouldReceive('hasArgument')->andReturn(false);
    $input->shouldReceive('validate')->andReturn(null);

    $this->conn->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', Mockery::type('array'))
        ->once();

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report',
        'owner' => [
            'name' => 'Owner',
        ],
        'data' => [
            'queries' => [
                [
                    'fields' => [[
                        'fieldName'    => 'id',
                        'bandName'     => 'main',
                        'displayLabel' => 'ID',
                        'isCurrency'   => false,
                    ]],
                    'params' => [],
                ],
            ],
        ],
    ]);

    $this->repo->shouldReceive('getReport')->with(123)->andReturn($mockReport);
    $this->executeReportUseCase->shouldReceive('executeReport')->andReturn([[], 0]);

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0);
});

it('execute runs with stats option', function (): void {
    $input = Mockery::mock(InputInterface::class);
    $output = new BufferedOutput();

    $input->shouldReceive('getArgument')->with('id')->andReturn('');
    $input->shouldReceive('getOption')->with('stats')->andReturn(true);
    $input->shouldReceive('bind')->andReturn(null);
    $input->shouldReceive('isInteractive')->andReturn(false);
    $input->shouldReceive('hasArgument')->andReturn(false);
    $input->shouldReceive('validate')->andReturn(null);

    $this->conn->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', Mockery::type('array'))
        ->once();

    $reports = [
        [
            'id'          => 1,
            'report_type' => '2',
            'owner'       => 100,
        ],
    ];

    $this->repo->shouldReceive('getReportListFlat')->andReturn($reports);

    $generator = (function () {
        yield [
            'id'     => 100,
            'name'   => 'Owner 1',
            'active' => 'Y',
        ];
    })();

    $this->repo->shouldReceive('getReportOwners')->andReturn($generator);

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0);
});

it('execute runs for all reports', function (): void {
    $input = Mockery::mock(InputInterface::class);
    $output = new BufferedOutput();

    $input->shouldReceive('getArgument')->with('id')->andReturn('');
    $input->shouldReceive('getOption')->with('stats')->andReturn(false);
    $input->shouldReceive('bind')->andReturn(null);
    $input->shouldReceive('isInteractive')->andReturn(false);
    $input->shouldReceive('hasArgument')->andReturn(false);
    $input->shouldReceive('validate')->andReturn(null);

    $this->conn->shouldReceive('procedure')
        ->with('acl.pacl.authorize_from_cp', Mockery::type('array'))
        ->once();

    $reports = [
        [
            'id'          => 100,
            'report_type' => '2',
            'owner'       => 100,
        ],
        [
            'id'          => 200,
            'report_type' => '1',
            'owner'       => 100,
        ], // Folder - should skip
        [
            'id'          => 10762,
            'report_type' => '2',
            'owner'       => 100,
        ], // Long execute - should skip
        [
            'id'          => 11800,
            'report_type' => '2',
            'owner'       => 100,
        ], // With errors - should skip
    ];

    $this->repo->shouldReceive('getReportListFlat')->andReturn($reports);

    $mockReport = Mockery::mock(Report::class);
    $mockReport->shouldReceive('toArray')->andReturn([
        'name'  => 'Test Report',
        'owner' => [
            'name' => 'Owner',
        ],
        'data' => [
            'queries' => [
                [
                    'fields' => [[
                        'fieldName'    => 'id',
                        'bandName'     => 'main',
                        'displayLabel' => 'ID',
                        'isCurrency'   => false,
                    ]],
                    'params' => [],
                ],
            ],
        ],
    ]);

    $this->repo->shouldReceive('getReport')->with(100)->andReturn($mockReport);
    $this->executeReportUseCase->shouldReceive('executeReport')->andReturn([[], 0]);

    $exitCode = $this->command->run($input, $output);

    expect($exitCode)->toBe(0);
});
