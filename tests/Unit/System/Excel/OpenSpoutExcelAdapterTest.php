<?php

declare(strict_types=1);

use App\Gateway\WriteExcelGateway;
use App\System\Excel\OpenSpoutExcelAdapter;

beforeEach(function (): void {
    $this->adapter = new OpenSpoutExcelAdapter();
});

it('implements WriteExcelGateway', function (): void {
    expect($this->adapter)->toBeInstanceOf(WriteExcelGateway::class);
});

it('initializes with default sheet', function (): void {
    // После создания должен быть активный лист Sheet1
    expect($this->adapter->clear())->toBe($this->adapter);
});

it('sets data to cells', function (): void {
    $data = [['A1', 'B1'], ['A2', 'B2']];

    $result = $this->adapter->setData($data, 'A1');

    expect($result)->toBe($this->adapter);
});

it('sets single row data', function (): void {
    $data = ['value1', 'value2', 'value3'];

    $result = $this->adapter->setData($data, 'A1');

    expect($result)->toBe($this->adapter);
});

it('sets title to sheet', function (): void {
    $result = $this->adapter->setTitle('Test Sheet');

    expect($result)->toBe($this->adapter);
});

it('sets auto size for column', function (): void {
    $result = $this->adapter->setAutoSize('A');

    expect($result)->toBe($this->adapter);
});

it('clears all data and resets to default', function (): void {
    $this->adapter->setData([['test']], 'A1');
    $this->adapter->setTitle('Custom');

    $result = $this->adapter->clear();

    expect($result)->toBe($this->adapter);
});

it('writes to file without errors', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';

    $this->adapter->setData([['Header1', 'Header2'], ['Value1', 'Value2']], 'A1');
    $this->adapter->writeFile($tempFile);

    expect(file_exists($tempFile))->toBeTrue()
        ->and(filesize($tempFile))->toBeGreaterThan(0);

    unlink($tempFile);
});

it('throws exception when selecting non-existent sheet', function (): void {
    expect(fn () => $this->adapter->selectSheet('NonExistent'))
        ->toThrow(RuntimeException::class, "Sheet 'NonExistent' does not exist");
});

it('throws exception when trying to iterate columns', function (): void {
    expect(fn () => $this->adapter->columns())
        ->toThrow(RuntimeException::class, 'OpenSpout does not support column iteration');
});

it('throws exception when trying to iterate rows', function (): void {
    expect(fn () => $this->adapter->rows())
        ->toThrow(RuntimeException::class, 'OpenSpout does not support row iteration');
});

it('converts column letter to index correctly', function (): void {
    expect($this->adapter->indexToLetter(0))->toBe('A')
        ->and($this->adapter->indexToLetter(25))->toBe('Z')
        ->and($this->adapter->indexToLetter(26))->toBe('AA')
        ->and($this->adapter->indexToLetter(27))->toBe('AB');
});

it('handles multiple sheets', function (): void {
    $this->adapter->addSheet('Sheet2');
    $this->adapter->selectSheet('Sheet2');
    $this->adapter->setData([['Data in Sheet2']], 'A1');

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('removes sheet successfully', function (): void {
    $this->adapter->addSheet('ToRemove');
    $this->adapter->removeSheet('ToRemove');

    expect(fn () => $this->adapter->selectSheet('ToRemove'))
        ->toThrow(RuntimeException::class);
});

it('sets cell value at specific address', function (): void {
    $this->adapter->setCellValue('B2', 'test value');

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('writes row with numeric index', function (): void {
    $this->adapter->writeRow(1, ['A', 'B', 'C']);

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('writes column with letter index', function (): void {
    $this->adapter->writeColumn('A', ['val1', 'val2', 'val3']);

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('sets header with row object', function (): void {
    $row = new class() {
        public function getRowIndex(): int
        {
            return 1;
        }
    };

    $this->adapter->setHeader($row);

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('applies header styling when writing file', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_header_') . '.xlsx';

    $row = new class() {
        public function getRowIndex(): int
        {
            return 1;
        }
    };

    $this->adapter->setData([['Header1', 'Header2']], 'A1');
    $this->adapter->setHeader($row);
    $this->adapter->writeFile($tempFile);

    expect(file_exists($tempFile))->toBeTrue();

    unlink($tempFile);
});

it('handles empty data gracefully', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_empty_') . '.xlsx';

    $this->adapter->writeFile($tempFile);

    expect(file_exists($tempFile))->toBeTrue();

    unlink($tempFile);
});

it('calculates column widths for auto-size', function (): void {
    $this->adapter->setAutoSize('A');
    $this->adapter->setData([['Short'], ['Very long text value']], 'A1');

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('handles data with different starting cell addresses', function (): void {
    $this->adapter->setData([['Data']], 'C5');

    expect($this->adapter)->toBeInstanceOf(OpenSpoutExcelAdapter::class);
});

it('closes file properly after writing', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_close_') . '.xlsx';

    $this->adapter->setData([['Test']], 'A1');
    $this->adapter->writeFile($tempFile);
    $this->adapter->closeFile();

    expect(file_exists($tempFile))->toBeTrue();

    unlink($tempFile);
});
