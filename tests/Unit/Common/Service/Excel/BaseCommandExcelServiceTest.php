<?php

declare(strict_types=1);

use App\Common\Service\Excel\BaseCommandExcelService;
use App\Common\Service\File\TempFileRegistry;
use App\Gateway\WriteExcelGateway;
use PhpOffice\PhpSpreadsheet\Worksheet\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

beforeEach(function (): void {
    $this->writer = Mockery::mock(WriteExcelGateway::class);
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->service = new class($this->writer, $this->tempFileRegistry) extends BaseCommandExcelService {
        public function setTabsPublic(array $tabs): static
        {
            return $this->setTabs($tabs);
        }

        public function eachItemPublic(Closure $fn, iterable $items): static
        {
            return $this->eachItem($fn, $items);
        }

        public function selectSheetPublic(string $sheetName): static
        {
            return $this->selectSheet($sheetName);
        }

        public function writeFilePublic(string $name, string $type = 'Xlsx'): void
        {
            $this->writeFile($name, $type);
        }

        public function setDefaultConfigPublic(): static
        {
            return $this->setDefaultConfig();
        }

        public function __construct(WriteExcelGateway $writer, TempFileRegistry $tempFileRegistry)
        {
            parent::__construct($writer, $tempFileRegistry);
            $this->fileName = 'test_file';
        }
    };
});

afterEach(function (): void {
    Mockery::close();
});

it('clears the writer and returns self', function (): void {
    $this->writer->shouldReceive('clear')->once();

    $result = $this->service->clear();

    expect($result)->toBe($this->service);
});

it('sets tabs correctly with single tab', function (): void {
    $tabs = ['Sheet1'];

    $this->writer->shouldReceive('setTitle')
        ->once()
        ->with('Sheet1');

    $result = $this->service->setTabsPublic($tabs);

    expect($result)->toBe($this->service);
});

it('sets tabs correctly with multiple tabs', function (): void {
    $tabs = ['Sheet1', 'Sheet2', 'Sheet3'];

    $this->writer->shouldReceive('setTitle')
        ->once()
        ->with('Sheet1');
    $this->writer->shouldReceive('addSheet')
        ->once()
        ->with('Sheet2');
    $this->writer->shouldReceive('addSheet')
        ->once()
        ->with('Sheet3');

    $result = $this->service->setTabsPublic($tabs);

    expect($result)->toBe($this->service);
});

it('processes items correctly with eachItem', function (): void {
    $items = [
        [
            'name' => 'John',
            'age'  => 30,
        ],
        [
            'name' => 'Jane',
            'age'  => 25,
        ],
    ];

    $fn = fn ($item, $row): array => [
        'Name' => $item['name'],
        'Age'  => $item['age'],
    ];

    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['Name', 'Age']);
    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['John', 30], 'A2');
    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['Jane', 25], 'A3');

    $result = $this->service->eachItemPublic($fn, $items);

    expect($result)->toBe($this->service);
});

it('handles empty items array', function (): void {
    $items = [];
    $fn = fn ($item, $row): array => [
        'data' => $item,
    ];

    $result = $this->service->eachItemPublic($fn, $items);

    expect($result)->toBe($this->service);
});

it('selects sheet correctly', function (): void {
    $sheetName = 'TestSheet';

    $this->writer->shouldReceive('selectSheet')
        ->once()
        ->with($sheetName);

    $result = $this->service->selectSheetPublic($sheetName);

    expect($result)->toBe($this->service);
});

it('writes file with default type', function (): void {
    $name = 'test_file';

    $this->writer->shouldReceive('writeFile')
        ->once()
        ->with($name, 'Xlsx');

    $this->service->writeFilePublic($name);
});

it('writes file with custom type', function (): void {
    $name = 'test_file';
    $type = 'Csv';

    $this->writer->shouldReceive('writeFile')
        ->once()
        ->with($name, $type);

    $this->service->writeFilePublic($name, $type);
});

it('sets default config correctly', function (): void {
    $column1 = Mockery::mock(Column::class);
    $column1->shouldReceive('getColumnIndex')->andReturn('A');
    $column2 = Mockery::mock(Column::class);
    $column2->shouldReceive('getColumnIndex')->andReturn('B');

    $row1 = Mockery::mock(Row::class);
    $row1->shouldReceive('getRowIndex')->andReturn(1);
    $row2 = Mockery::mock(Row::class);
    $row2->shouldReceive('getRowIndex')->andReturn(2);

    // Mock the specific iterator types that are expected
    $columnIterator = Mockery::mock(ColumnIterator::class);
    $columnIterator->shouldReceive('rewind');
    $columnIterator->shouldReceive('valid')->andReturn(true, true, false);
    $columnIterator->shouldReceive('current')->andReturn($column1, $column2);
    $columnIterator->shouldReceive('next');

    $rowIterator = Mockery::mock(RowIterator::class);
    $rowIterator->shouldReceive('rewind');
    $rowIterator->shouldReceive('valid')->andReturn(true, true, false);
    $rowIterator->shouldReceive('current')->andReturn($row1, $row2);
    $rowIterator->shouldReceive('next');

    $this->writer->shouldReceive('columns')
        ->once()
        ->andReturn($columnIterator);
    $this->writer->shouldReceive('rows')
        ->once()
        ->andReturn($rowIterator);

    $this->writer->shouldReceive('setAutoSize')
        ->once()
        ->with('A');
    $this->writer->shouldReceive('setAutoSize')
        ->once()
        ->with('B');
    $this->writer->shouldReceive('setHeader')
        ->once()
        ->with($row1);

    $result = $this->service->setDefaultConfigPublic();

    expect($result)->toBe($this->service);
});

it('processes complex data transformation', function (): void {
    $items = [
        [
            'id'    => 1,
            'name'  => 'Product A',
            'price' => 99.99,
        ],
        [
            'id'    => 2,
            'name'  => 'Product B',
            'price' => 149.99,
        ],
    ];

    $fn = function (array $item, $row): array {
        return [
            'ID'           => $item['id'],
            'Product Name' => strtoupper($item['name']),
            'Price ($)'    => number_format($item['price'], 2),
            'Row Number'   => $row,
        ];
    };

    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['ID', 'Product Name', 'Price ($)', 'Row Number']);
    $this->writer->shouldReceive('setData')
        ->once()
        ->with([1, 'PRODUCT A', '99.99', 2], 'A2');
    $this->writer->shouldReceive('setData')
        ->once()
        ->with([2, 'PRODUCT B', '149.99', 3], 'A3');

    $result = $this->service->eachItemPublic($fn, $items);

    expect($result)->toBe($this->service);
});

it('handles single item correctly', function (): void {
    $items = [[
        'single' => 'value',
    ]];
    $fn = fn ($item, $row): array => [
        'Single' => $item['single'],
    ];

    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['Single']);
    $this->writer->shouldReceive('setData')
        ->once()
        ->with(['value'], 'A2');

    $result = $this->service->eachItemPublic($fn, $items);

    expect($result)->toBe($this->service);
});

it('chains multiple operations correctly', function (): void {
    $tabs = ['Report'];
    $items = [[
        'test' => 'data',
    ]];
    $fn = fn ($item, $row): array => [
        'Test' => $item['test'],
    ];

    $this->writer->shouldReceive('setTitle')->once()->with('Report');
    $this->writer->shouldReceive('setData')->once()->with(['Test']);
    $this->writer->shouldReceive('setData')->once()->with(['data'], 'A2');
    $this->writer->shouldReceive('selectSheet')->once()->with('Report');

    $result = $this->service
        ->setTabsPublic($tabs)
        ->eachItemPublic($fn, $items)
        ->selectSheetPublic('Report');

    expect($result)->toBe($this->service);
});

it('creates and returns uploaded file', function (): void {
    $uploadedFile = Mockery::mock(UploadedFile::class);
    $uploadedFile->shouldReceive('getPathname')->andReturn('/tmp/test_file.xlsx');

    $this->tempFileRegistry->shouldReceive('createUploadedFile')
        ->once()
        ->with('test_file.xlsx')
        ->andReturn($uploadedFile);

    $this->writer->shouldReceive('writeFile')
        ->once()
        ->with('/tmp/test_file.xlsx', 'Xlsx');

    $result = $this->service->getFile();

    expect($result)->toBe($uploadedFile);
});
