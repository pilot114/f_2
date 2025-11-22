# Common

Общие компоненты для использования во всех доменах системы.

## Содержание

- [Attributes](#attributes)
- [DTO](#dto)
- [Exceptions](#exceptions)
- [Helpers](#helpers)
- [Services](#services)

---

## Attributes

### RpcMethod

**Назначение**: Атрибут для методов RPC-контроллеров. Описывает метод в OpenRPC спецификации.

**Использование**: Добавляется к публичным методам RPC-контроллеров.

```php
use App\Common\Attribute\{RpcMethod, RpcParam};

#[RpcMethod(
    name: 'user.profile.get',
    summary: 'Получить профиль пользователя',
    description: 'Возвращает данные профиля по ID пользователя',
    errors: [404 => 'Пользователь не найден'],
    examples: [
        ['userId' => 123]
    ],
    tags: ['user'],
    isDeprecated: true,
    isAutomapped: true,
)]
public function getProfile(
    #[RpcParam(summary: 'ID пользователя', required: true)]
    int $userId
): UserProfileDto {
    // ...
}
```

**Параметры**:
- `name` (string) - имя метода в формате `domain.subdomain.usecase`
- `summary` (string) - краткое описание
- `description` (?string) - подробное описание
- `errors` (array) - ошибки метода
- `examples` (array) - примеры вызова
- `tags` (array) - теги для группировки
- `isDeprecated` (bool) - маркер устаревшего метода
- `isAutomapped` (bool) - автомаппинг параметров в DTO

**Публичные методы**:
- `isQuery(): bool` - проверяет, является ли метод запросом (содержит в названии get/find/search/check)

---

### RpcParam

**Назначение**: Атрибут для параметров RPC-методов. Описывает параметр в OpenRPC спецификации.

**Использование**: Добавляется к параметрам методов или свойствам DTO.

```php
use App\Common\Attribute\RpcParam;

class CreateUserDto
{
    public function __construct(
        #[RpcParam(
            summary: 'Email пользователя',
            description: 'Должен быть уникальным в системе',
            required: true
        )]
        public string $email,

        #[RpcParam(summary: 'Имя пользователя', required: false)]
        public ?string $name = null,
    ) {}
}
```

---

### CpAction

**Назначение**: Проверка прав доступа к действиям в пользовательском интерфейсе портала.

**Использование**: Добавляется к методам контроллеров. Поддерживает логические выражения.

```php
use App\Common\Attribute\CpAction;

#[CpAction('user.edit')]
public function editUser(int $userId): void
{
    // Выполнится только если у пользователя есть право 'user.edit'
}

#[CpAction('user.edit or user.view')]
public function viewOrEdit(int $userId): void
{
    // Выполнится если есть хотя бы одно из прав
}
```

---

### CpMenu

**Назначение**: Проверка прав доступа к пунктам меню корпоративного портала.

**Использование**: Добавляется к методам контроллеров.

```php
use App\Common\Attribute\CpMenu;

#[CpMenu('admin.users')]
public function usersList(): array
{
    // Выполнится только если у пользователя есть доступ к меню 'admin.users'
}
```

---

## DTO

### FindRequest

**Назначение**: Базовый DTO для запросов с пагинацией.

**Использование**: Наследуйте для создания запросов с пагинацией.

```php
use App\Common\DTO\FindRequest;

class FindUsersRequest extends FindRequest
{
    public function __construct(
        public ?string $search = null,
        public ?string $role = null,
        ?int $page = null,
        ?int $perPage = null,
    ) {
        parent::__construct($page, $perPage);
    }
}
```

---

### FindResponse

**Назначение**: DTO для возврата коллекции с общим количеством элементов.

**Использование**: Используется для возврата списков с пагинацией.

```php
use App\Common\DTO\FindResponse;

public function findUsers(FindUsersRequest $request): FindResponse
{
    $users = $this->repository->find($request);

    return new FindResponse(
        items: $users,  // array или Enumerable
        total: 150      // опционально, если не указан - count($items)
    );
}

// С Enumerable (автоматически определит total через getTotal())
use App\Common\Helper\EnumerableWithTotal;

$users = EnumerableWithTotal::build($queryResult, total: 150);
return new FindResponse($users);
```

---

### FindItemResponse

**Назначение**: Базовый DTO для элемента списка с id и name.

**Использование**: Используйте для простых элементов списков или наследуйте.

```php
use App\Common\DTO\FindItemResponse;

// Прямое использование
$items = [
    new FindItemResponse(id: 1, name: 'User 1'),
    new FindItemResponse(id: 2, name: 'User 2'),
];

// Наследование для расширения
class UserItemResponse extends FindItemResponse
{
    public function __construct(
        int $id,
        string $name,
        public string $email,
        public bool $isActive,
    ) {
        parent::__construct($id, $name);
    }
}
```

---

### FilterOption

**Назначение**: Enum для опций фильтрации (ANY/SOME/NONE).

**Использование**: Используйте для фильтров с квантификаторами.

```php
use App\Common\DTO\FilterOption;

class FindProductsRequest
{
    public function __construct(
        public FilterOption $tagsFilter = FilterOption::Q_ANY,
    ) {}
}

// Q_ANY - значение любое
// Q_SOME - значение IS NOT NULL
// Q_NONE - значение IS NULL
```

---

### Titleable

**Назначение**: Интерфейс для Enum'ов с человекочитаемыми названиями.

**Использование**: Имплементируйте в Enum для автоматического отображения названий в API.

```php
use App\Common\DTO\Titleable;

enum UserRole: string implements Titleable
{
    case ADMIN = 'admin';
    case USER = 'user';
    case GUEST = 'guest';

    public function getTitle(): string
    {
        return match($this) {
            self::ADMIN => 'Администратор',
            self::USER => 'Пользователь',
            self::GUEST => 'Гость',
        };
    }
}

// При возврате в API автоматически добавится поле title
// {"name": "ADMIN", "value": "admin", "title": "Администратор"}
```

---

## Helpers

### CountableFormatter

**Назначение**: Форматирование чисел с правильным склонением существительных.

**Использование**: Статический метод для склонения русских слов.

```php
use App\Common\Helper\CountableFormatter;

// pluralize(число, [форма1, форма2, форма5], показыватьПриНуле)
echo CountableFormatter::pluralize(1, ['год', 'года', 'лет']);    // "1 год"
echo CountableFormatter::pluralize(2, ['год', 'года', 'лет']);    // "2 года"
echo CountableFormatter::pluralize(5, ['год', 'года', 'лет']);    // "5 лет"
echo CountableFormatter::pluralize(21, ['год', 'года', 'лет']);   // "21 год"

echo CountableFormatter::pluralize(0, ['товар', 'товара', 'товаров']);        // ""
echo CountableFormatter::pluralize(0, ['товар', 'товара', 'товаров'], false); // "0 товаров"

// Примеры использования
$count = 3;
echo CountableFormatter::pluralize($count, ['найден', 'найдено', 'найдено']);
echo CountableFormatter::pluralize($count, ['пользователь', 'пользователя', 'пользователей']);
```

---

### DateHelper

**Назначение**: Работа с датами и форматирование на русском языке.

**Использование**: Создание экземпляра или статические методы.

```php
use App\Common\Helper\DateHelper;
use DateTimeImmutable;

// Через конструктор
$helper = new DateHelper('2024-03-15');
echo $helper->getRussianMonthAndYear(); // "Март 2024"

// Статический метод для форматирования
$date = new DateTimeImmutable('2024-06-05');
echo DateHelper::ruDateFormat($date);                    // "5 июня 2024"
echo DateHelper::ruDateFormat($date, 'dd.MM.yyyy');      // "05.06.2024"
echo DateHelper::ruDateFormat($date, 'LLLL yyyy');       // "июнь 2024"

// Массив названий месяцев
$months = DateHelper::MONTH_NAMES; // ['Январь', 'Февраль', ...]
```

---

### PeriodFormatter

**Назначение**: Форматирование периодов (месяц, периоды по 2 месяца, квартал).

**Использование**: Статические методы для получения названий периодов.

```php
use App\Common\Helper\PeriodFormatter;
use DateTimeImmutable;

$date = new DateTimeImmutable('2024-03-15');

// Месячный период
echo PeriodFormatter::getMonthlyPeriodTitle($date);   // "март 2024"

// Два месяца
echo PeriodFormatter::getBimonthlyPeriodTitle($date); // "март-апрель 2024"

// Квартал
echo PeriodFormatter::getQuarterlyPeriodTitle($date); // "I квартал 2024"

$dateQ4 = new DateTimeImmutable('2024-12-15');
echo PeriodFormatter::getQuarterlyPeriodTitle($dateQ4); // "IV квартал 2024"
```

---

### EnumerableWithTotal

**Назначение**: Создание Collection/LazyCollection с методом `getTotal()`.

**Использование**: Используйте для коллекций с известным total (например, для пагинации).

```php
use App\Common\Helper\EnumerableWithTotal;

// С массивом
$items = [1, 2, 3, 4, 5];
$collection = EnumerableWithTotal::build($items, total: 100);
echo $collection->getTotal(); // 100

// С итератором (вернёт LazyCollection)
$iterator = $this->repository->getCursor();
$lazyCollection = EnumerableWithTotal::build($iterator, total: 1000);
echo $lazyCollection->getTotal(); // 1000

// Использование с FindResponse
$users = EnumerableWithTotal::build($userIterator, total: 500);
return new FindResponse($users); // total автоматически подставится
```

---

## Services

### Integration

#### StaticClient

**Назначение**: Клиент для работы с сервисом хранения файлов.

**Использование**: Загрузка, удаление файлов, генерация URL с ресайзом.

```php
use App\Common\Service\Integration\StaticClient;
use App\Domain\Portal\Files\Enum\ImageSize;
use App\Domain\Portal\Files\Enum\ImageResizeType;
use Symfony\Component\HttpFoundation\File\File;

// Загрузка файла
$file = new File('/tmp/image.jpg');
$url = $staticClient->uploadFile(
    file: $file,
    name: 'avatar.jpg',
    directory: '/cp_userpic/',
    isRewrite: false
);
// Результат: /public/cp_userpic/uniqid_avatar.jpg

// Удаление файла
$removed = $staticClient->removeFile('/public/cp_userpic/avatar.jpg');

// Конвертация имени файла в безопасное
$safeName = $staticClient->convertToSafeName('Мой Файл.txt'); // "moy_fayl.txt"

// Получение URL аватара пользователя с ресайзом
$url = StaticClient::getUserpicByUserId(empId: 123, size: ImageSize::SIZE_80);
// https://static.siberianhealth.com/public/cp_userpic/_resize/123_fit_80_80.jpg

// Добавление ресайза к любому URL
$originalUrl = 'https://static.siberianhealth.com/public/images/photo.jpg';
$resizedUrl = StaticClient::getResizeUrl(
    url: $originalUrl,
    size: ImageSize::SIZE_150,
    type: ImageResizeType::CROP
);
// https://static.siberianhealth.com/public/images/_resize/photo_crop_150_150.jpg
```

**Константы**:
- `MAX_FILE_SIZE = 8388608` (8MB) - максимальный размер файла

---

#### RpcClient

**Назначение**: Клиент для вызова RPC-методов внутри системы.

**Использование**: Межсервисное взаимодействие через RPC.

```php
use App\Common\Service\Integration\RpcClient;

// Вызов RPC-метода
$result = $rpcClient->call(
    method: 'user.profile.get',
    params: ['userId' => 123]
);

// Вызов без параметров
$list = $rpcClient->call('product.catalog.list');
```

---

#### JiraClient

**Назначение**: Клиент для работы с Jira API.

**Использование**: Получение задач по JQL-запросам.

```php
use App\Common\Service\Integration\JiraClient;

// Получить все issues по JQL
$issues = $jiraClient->getAllIssuesByJql(
    jql: 'project = MYPROJ AND status = "In Progress"',
    fields: ['summary', 'status', 'assignee'],
    expand: ['changelog'],
    start: 0,
    limit: 100
);

// Получить количество issues
$count = $jiraClient->getIssuesCount('project = MYPROJ');
```

---

#### ProductImageClient

**Назначение**: Клиент для получения изображений продуктов (в данный момент не используется).

**Использование**: Получение URL изображений по кодам продуктов.

```php
use App\Common\Service\Integration\ProductImageClient;
use App\Common\DTO\ProductImageSize;

$client = $container->get(ProductImageClient::class);

// Установить страну
$client->setCountry('ru');

// Получить изображения
$codes = collect(['500020', '500632', '500105']);
$images = $client->get($codes, ProductImageSize::SIZE150);
// Collection: ['500020' => 'url1', '500632' => 'url2', ...]
```

---

### File

#### TempFileRegistry

**Назначение**: Безопасное создание временных файлов с автоочисткой.

**Использование**: Создавайте временные файлы, которые удалятся после запроса.

```php
use App\Common\Service\File\TempFileRegistry;

// Создать временный File
$file = $tempFileRegistry->createFile('содержимое файла');

// Создать временный UploadedFile с читаемым именем
$uploadedFile = $tempFileRegistry->createUploadedFile(
    readableName: 'document.pdf',
    content: $pdfContent
);

// Ручная очистка (обычно не требуется)
$tempFileRegistry->clear();
```

---

#### ImageBase64

**Назначение**: Конвертация base64 изображений в файлы.

**Использование**: Принять base64 из запроса и сохранить как File.

```php
use App\Common\Service\File\ImageBase64;

$base64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRg...';

// Конвертировать в File
$file = $imageBase64->baseToFile($base64);

// С кастомным лимитом размера
$file = $imageBase64->baseToFile($base64, maxFileSize: 5 * 1024 * 1024); // 5MB
```

**Константы**:
- `MAX_IMAGE_SIZE = 8388608` (8MB)

**Исключения**:
- `BadRequestHttpException` - невалидный base64 или не изображение
- `UnsupportedMediaTypeHttpException` - неподдерживаемый формат

---

#### FileService

**Назначение**: Связывает статик-сервер и БД файлов, обеспечивая консистентность.

**Использование**: Основной сервис для работы с файлами.

```php
use App\Common\Service\File\FileService;
use Symfony\Component\HttpFoundation\File\File;

// Загрузка файла
$file = new File('/tmp/document.pdf');
$fileEntity = $fileService->commonUpload(
    file: $file,
    collectionName: 'documents',
    idInCollection: 42  // опционально
);

// Получить файл по ID
$file = $fileService->getById(fileId: 123);
$file = $fileService->getById(fileId: 123, userId: 456); // с проверкой владельца

// Получить список файлов пользователя
$files = $fileService->getFileListByUserId(userId: 123);
$avatars = $fileService->getFileListByUserId(userId: 123, collectionName: 'userpic');

// Удалить файл
$deleted = $fileService->commonDelete(fileId: 123, userId: 456);

// Получить URL файла на статике
$url = $fileService->getStaticUrl($file);
$resizedUrl = $fileService->getStaticUrl($file, resizeString: 'fit_150');

// Получить изображение с ресайзом
$image = $fileService->getResizedImage($file, resizeString: 'crop_80');

// Получить заголовки кеширования
$headers = $fileService->getCacheHeaders($file);
// ['last_modified' => DateTimeImmutable, 'max_age' => 3600]
```

---

#### AvatarService

**Назначение**: Специализированный сервис для работы с аватарами.

**Использование**: Наследует FileService, добавляет метод для получения аватара.

```php
use App\Common\Service\File\AvatarService;

// Получить аватар пользователя
$avatar = $avatarService->getAvatar(userId: 123);

// Все методы FileService также доступны
$url = $avatarService->getStaticUrl($avatar, resizeString: 'fit_80');
```

---

### Excel

#### ExcelExporterInterface

**Назначение**: Интерфейс для создания Excel-экспортёров.

**Использование**: Имплементируйте для создания кастомных экспортёров.

```php
use App\Common\Service\Excel\ExcelExporterInterface;

class UsersExcelExporter implements ExcelExporterInterface
{
    public function getExporterName(): string
    {
        return 'users';
    }

    public function getFileName(): string
    {
        return 'users_export_' . date('Y-m-d') . '.xlsx';
    }

    public function export(array $params): void
    {
        // Логика экспорта с прямым стримом в браузер
        $spreadsheet = new Spreadsheet();
        // ...
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
```

---

#### ExcelExportService

**Назначение**: Сервис для запуска экспорта в Excel.

**Использование**: Используйте в контроллерах для экспорта данных.

```php
use App\Common\Service\Excel\ExcelExportService;

// В контроллере
public function exportUsers(array $filters): StreamedResponse
{
    return $this->excelExportService->export(
        exporterName: 'users',
        params: $filters
    );
}
```

---

#### BaseCommandExcelService

**Назначение**: Базовый класс для создания Excel-файлов в командах.

**Использование**: Наследуйте для создания сервисов с собаственной бизнес-логикой.

```php
use App\Common\Service\Excel\BaseCommandExcelService;

class ReportExcelService extends BaseCommandExcelService
{
    protected string $fileName = 'monthly_report';

    public function generate(array $data): UploadedFile
    {
        $this->clear()
            ->setTabs(['Отчёт', 'Детализация'])
            ->eachItem(
                fn($item, $row) => [
                    'ID' => $item->id,
                    'Название' => $item->name,
                    'Сумма' => $item->amount,
                ],
                $data
            )
            ->setDefaultConfig();

        return $this->getFile();
    }
}

// Использование
$file = $reportService->generate($reportData);
// Вернёт UploadedFile с Excel
```

**Методы**:
- `setTabs(array $tabs)` - создать вкладки
- `eachItem(Closure $fn, iterable $items)` - заполнить данными
- `selectSheet(string $name)` - выбрать вкладку
- `setDefaultConfig()` - установить автоширину и заголовки
- `getFile(): UploadedFile` - получить файл
- `clear()` - очистить данные

---

## Общие рекомендации

### Attributes
- `RpcMethod`, `RpcParam`, `CpAction` и `CpMenu` - только для RPC-контроллеров
- Не используйте атрибуты вручную, они обрабатываются автоматически

### DTO
- `FindRequest`/`FindResponse` - стандарт для всех find-методов
- `Titleable` - для всех enum'ов, отображаемых пользователю
- `FilterOption` - для фильтров с квантификаторами

### Exceptions
- Наследуйте `DomainException` для доменных ошибок
- `InvariantDomainException` - только в Entity для защиты инвариантов

### Helpers
- `CountableFormatter` - для всех чисел с существительными на русском
- `DateHelper`/`PeriodFormatter` - для дат на русском языке
- `EnumerableWithTotal` - для всех пагинированных коллекций

### Services
- `StaticClient` - использовать только через `FileService`, напрямую только в особых случаях
- `FileService` - основной способ работы с файлами
- `TempFileRegistry` - для временных файлов (автоочистка)
- `RpcClient` - для взаимодействия cо старым back (следует минимизировать использование)
- Excel-сервисы: `ExcelExportService` и `BaseCommandExcelService`
