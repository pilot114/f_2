<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\UseCase;

use App\Domain\Analytics\Mcp\Entity\Artefact;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use App\Domain\Analytics\Mcp\Retriever\OracleArtefactRetriever;
use App\Domain\Analytics\Mcp\UseCase\GetArtefactUseCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->cacheRetriever = Mockery::mock(CacheArtefactRetriever::class);
    $this->oracleRetriever = Mockery::mock(OracleArtefactRetriever::class);
    $this->useCase = new GetArtefactUseCase($this->cacheRetriever, $this->oracleRetriever);
});

it('throws NotFoundHttpException when artefact not found', function (): void {
    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('TEST.TABLE', ArtefactType::TABLE)
        ->andReturn(null);

    expect(fn () => $this->useCase->processNestedLinks('test.table', ArtefactType::TABLE, true))
        ->toThrow(NotFoundHttpException::class, 'Не найдена сущность test.table');
});

it('returns artefact data when found', function (): void {
    $artefact = Mockery::mock(Artefact::class);
    $artefact->shouldReceive('toArray')->andReturn([
        'name' => 'TEST.TABLE',
    ]);
    $artefact->shouldReceive('getContent')->andReturn((object) [
        'links' => [],
    ]);

    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('TEST.TABLE', ArtefactType::TABLE)
        ->andReturn($artefact);

    $result = $this->useCase->processNestedLinks('test.table', ArtefactType::TABLE, true);

    expect($result)->toEqual([
        'artefact' => [
            'name' => 'TEST.TABLE',
        ],
        'depends' => [],
    ]);
});

it('processes nested links with multiple depth levels', function (): void {
    $mainArtefact = Mockery::mock(Artefact::class);
    $mainArtefact->shouldReceive('toArray')->andReturn([
        'name' => 'MAIN.TABLE',
    ]);
    $mainArtefact->shouldReceive('getContent')->andReturn((object) [
        'links' => [],
    ]);

    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('MAIN.TABLE', ArtefactType::TABLE)
        ->andReturn($mainArtefact);

    $result = $this->useCase->processNestedLinks('main.table', ArtefactType::TABLE, true);

    expect($result)->toHaveKey('artefact');
    expect($result)->toHaveKey('depends');
    expect($result['depends'])->toBeEmpty();
});

it('handles size limit correctly', function (): void {
    $artefact = Mockery::mock(Artefact::class);
    $artefact->shouldReceive('toArray')->andReturn([
        'name' => 'LARGE.TABLE',
    ]);
    $artefact->shouldReceive('getContent')->andReturn((object) [
        'links' => [],
    ]);

    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('LARGE.TABLE', ArtefactType::TABLE)
        ->andReturn($artefact);

    $result = $this->useCase->processNestedLinks('large.table', ArtefactType::TABLE, true);

    expect($result)->toHaveKey('artefact');
    expect($result)->toHaveKey('depends');
});

it('falls back to oracle retriever when cache miss and onlyCache is false', function (): void {
    $mainArtefact = Mockery::mock(Artefact::class);
    $mainArtefact->shouldReceive('toArray')->andReturn([
        'name' => 'MAIN.TABLE',
    ]);
    $mainArtefact->shouldReceive('getContent')->andReturn((object) [
        'links' => [],
    ]);

    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('MAIN.TABLE', ArtefactType::TABLE)
        ->andReturn($mainArtefact);

    $result = $this->useCase->processNestedLinks('main.table', ArtefactType::TABLE, false);

    expect($result['depends'])->toBeEmpty();
});

it('handles array links correctly', function (): void {
    $artefact = Mockery::mock(Artefact::class);
    $artefact->shouldReceive('toArray')->andReturn([
        'name' => 'MAIN.TABLE',
    ]);
    $artefact->shouldReceive('getContent')->andReturn((object) [
        'links' => [],
    ]);

    $this->cacheRetriever
        ->shouldReceive('get')
        ->with('MAIN.TABLE', ArtefactType::TABLE)
        ->andReturn($artefact);

    $result = $this->useCase->processNestedLinks('main.table', ArtefactType::TABLE, true);

    expect($result['depends'])->toBeEmpty();
});
