# Excel Export System

Система для экспорта данных в Excel файлы с использованием openspout библиотеки версии 4+.
https://github.com/openspout/openspout/blob/4.x/docs/documentation.md

## Использование

### 1. Отправка запроса на скачивание файла

```bash
POST /api/v2/excel
Content-Type: application/json

{
    "exporter": "ExampleExporter",
    "params": {"date_from": "2024-01-01"}
}
```

### 2. Создание базового экспортера

Для создания экспортера в любом домене:

Экспортеры должны имплементировать ExcelExporterInterface
Базовая реализация с дефолтными стилями - AbstractExporter

```php
<?php

namespace App\Domain\YourModule\Excel;

use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use App\Domain\Portal\Excel\Trait\ExcelExporterTrait;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use Psr\Log\LoggerInterface;

class BasicExporter extends AbstractExporter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        Writer $writer
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'BasicExporter';
    }

    public function getFileName(): string
    {
        $date = date('Y-m-d_H-i-s');
        return $this->sanitizeFileName("basic_export_$date.xlsx");
    }

    public function export(array $params): void
    {
        $this->writer->openToBrowser($this->getFileName());
        
        $data = $this->getExampleData();
        foreach ($data as $item) {
            $dataRow = Row::fromValues($item);
            $this->writer->addRow($dataRow);
        }
        
        $this->writer->close();
    }
    
     private function getExampleData(): array
    {
        return [
            [
                'ID',
                'Название',
                'Дата создания',
                'Статус',
                'Сумма',
            ],
        ];
    }
}
```

### 3. Создание экспортера со стилями

смотри App\Domain\Portal\Excel\Exporter\ExampleExporter
