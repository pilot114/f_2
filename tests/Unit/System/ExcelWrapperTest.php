<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\Gateway\ReadExcelGateway;
use App\Gateway\WriteExcelGateway;
use App\System\Excel\SpreadSheetExcelAdapter;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

beforeEach(function (): void {
    $this->wrapper = new SpreadSheetExcelAdapter();
});

it('реализует интерфейсы ReadExcelGateway и WriteExcelGateway', function (): void {
    expect($this->wrapper)->toBeInstanceOf(ReadExcelGateway::class)
        ->toBeInstanceOf(WriteExcelGateway::class);
});

it('возвращает данные из активного листа', function (): void {
    $data = [
        ['Name', 'Age', 'City'],
        ['John', 30, 'New York'],
        ['Jane', 25, 'London'],
    ];

    $this->wrapper->setData($data);

    $result = $this->wrapper->getData();

    expect($result)->toBeArray()
        ->toHaveCount(3)
        ->and($result[0])->toBe(['Name', 'Age', 'City'])
        ->and($result[1])->toBe(['John', '30', 'New York']);
});

it('записывает данные начиная с указанной ячейки', function (): void {
    $data = [
        ['Value1', 'Value2'],
        ['Value3', 'Value4'],
    ];

    $this->wrapper->setData($data, 'B2');

    $result = $this->wrapper->getData();

    expect($result)->toBeArray()
        ->and($result[1][1])->toBe('Value1')
        ->and($result[1][2])->toBe('Value2');
});

it('очищает данные и создаёт новый spreadsheet', function (): void {
    $this->wrapper->setData([['Test']]);

    $result = $this->wrapper->clear();

    expect($result)->toBeInstanceOf(SpreadSheetExcelAdapter::class);

    $data = $result->getData();
    expect($data)->toBeArray()
        ->and($data[0][0])->toBeNull();
});

it('устанавливает автоширину для колонки', function (): void {
    $result = $this->wrapper->setAutoSize('A');

    expect($result)->toBeInstanceOf(SpreadSheetExcelAdapter::class);
});

it('добавляет новый лист', function (): void {
    $this->wrapper->addSheet('TestSheet');
    $this->wrapper->selectSheet('TestSheet');

    $this->wrapper->setData([['Test Data']]);

    expect($this->wrapper->getData())->toBeArray()
        ->and($this->wrapper->getData()[0][0])->toBe('Test Data');
});

it('устанавливает заголовок листа', function (): void {
    $result = $this->wrapper->setTitle('MySheet');

    expect($result)->toBeInstanceOf(SpreadSheetExcelAdapter::class);
});

it('записывает файл', function (): void {
    $tempFile = sys_get_temp_dir() . '/test_excel_' . uniqid() . '.xlsx';

    $this->wrapper->setData([
        ['Header1', 'Header2'],
        ['Value1', 'Value2'],
    ]);

    $this->wrapper->writeFile($tempFile);

    expect(file_exists($tempFile))->toBeTrue()
        ->and(filesize($tempFile))->toBeGreaterThan(0);

    unlink($tempFile);
});

it('возвращает экземпляр самого себя при вызове openFile', function (): void {
    $result = $this->wrapper->openFile();

    expect($result)->toBeInstanceOf(SpreadSheetExcelAdapter::class)
        ->toBe($this->wrapper);
});

it('возвращает экземпляр самого себя при вызове selectSheet', function (): void {
    $this->wrapper->addSheet('NewSheet');
    $result = $this->wrapper->selectSheet('NewSheet');

    expect($result)->toBeInstanceOf(SpreadSheetExcelAdapter::class);
});

it('закрывает файл без ошибок', function (): void {
    $this->wrapper->setData([['Test']]);

    expect(fn () => $this->wrapper->closeFile())->not->toThrow(Exception::class);
});

it('возвращает итератор колонок', function (): void {
    $columns = $this->wrapper->columns();

    expect($columns)->toBeInstanceOf(ColumnIterator::class);
});

it('возвращает итератор строк', function (): void {
    $rows = $this->wrapper->rows();

    expect($rows)->toBeInstanceOf(RowIterator::class);
});

it('возвращает значение ячейки по умолчанию', function (): void {
    $value = $this->wrapper->getCellValue('A1');

    expect($value)->toBe(42);
});

it('возвращает пустой массив для значений строки', function (): void {
    $values = $this->wrapper->getRowValues(1);

    expect($values)->toBe([]);
});

it('возвращает пустой массив для значений колонки', function (): void {
    $values = $this->wrapper->getColumnValues('A');

    expect($values)->toBe([]);
});
