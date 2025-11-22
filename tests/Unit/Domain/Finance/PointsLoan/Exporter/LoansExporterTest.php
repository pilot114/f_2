<?php

declare(strict_types=1);

use App\Domain\Finance\PointsLoan\Exporter\LoansExporter;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('класс LoansExporter существует', function (): void {
    expect(class_exists('App\Domain\Finance\PointsLoan\Exporter\LoansExporter'))->toBeTrue();
});

it('имеет конструктор с зависимостями', function (): void {
    $reflection = new ReflectionClass('App\Domain\Finance\PointsLoan\Exporter\LoansExporter');
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();
});

it('extends AbstractExporter', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);

    expect($reflection->getParentClass()->getName())->toBe(AbstractExporter::class);
});

it('has required dependencies', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();
    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('loanQueryRepository')
        ->and($parameters[1]->getName())->toBe('writer');
});

it('has getExporterName method', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);
    $method = $reflection->getMethod('getExporterName');

    expect($method->isPublic())->toBeTrue();

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('string');
});

it('has getFileName method', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);
    $method = $reflection->getMethod('getFileName');

    expect($method->isPublic())->toBeTrue();

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('string');
});

it('has export method with correct signature', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);
    $method = $reflection->getMethod('export');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('params')
        ->and($parameters[0]->getType()?->getName())->toBe('array');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('void');
});

it('has private properties', function (): void {
    $reflection = new ReflectionClass(LoansExporter::class);

    expect($reflection->hasProperty('loanQueryRepository'))->toBeTrue()
        ->and($reflection->hasProperty('writer'))->toBeTrue();

    $loanQueryRepository = $reflection->getProperty('loanQueryRepository');
    expect($loanQueryRepository->isPrivate())->toBeTrue()
        ->and($loanQueryRepository->isReadOnly())->toBeTrue();
});

it('returns correct exporter name', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);

    expect($exporter->getExporterName())->toBe('LoansExporter');
});

it('returns file name with date', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);
    $fileName = $exporter->getFileName();

    expect($fileName)->toStartWith('loans_export_')
        ->and($fileName)->toEndWith('.xlsx')
        ->and($fileName)->toContain(date('Y-m-d'));
});

it('throws exception when start parameter is missing', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);

    expect(fn () => $exporter->export([
        'end' => '2024-01-01',
    ]))
        ->toThrow(HttpException::class, 'не переданы обязательные параметры start или end');
});

it('throws exception when end parameter is missing', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);

    expect(fn () => $exporter->export([
        'start' => '2024-01-01',
    ]))
        ->toThrow(HttpException::class, 'не переданы обязательные параметры start или end');
});

it('throws exception when date format is invalid', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);

    expect(fn () => $exporter->export([
        'start' => 'invalid',
        'end'   => '2024-01-01',
    ]))
        ->toThrow(HttpException::class, 'не удалось распознать формат дат');
});

it('validates parameters before processing', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $repo->shouldNotReceive('getLoansInExcelRepresentation');

    $exporter = new LoansExporter($repo, $writer);

    try {
        $exporter->export([]);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (HttpException $e) {
        expect($e->getMessage())->toContain('не переданы обязательные параметры');
    }
});

it('handles invalid end date format', function (): void {
    $repo = Mockery::mock(LoanQueryRepository::class);
    $writer = new Writer();

    $exporter = new LoansExporter($repo, $writer);

    expect(fn () => $exporter->export([
        'start' => '2024-01-01',
        'end'   => 'not-a-date',
    ]))
        ->toThrow(HttpException::class, 'не удалось распознать формат дат');
});

afterEach(function (): void {
    Mockery::close();
});
