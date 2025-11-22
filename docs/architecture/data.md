# Данные

### Общие принципы

В большинстве случаев, объекты более удобны для работы с данными (типизация, автодополнение, встроенная логика и пр.),
поэтому php массивы следует использовать только в некоторых ситуациях:
- есть уверенность, что данным не нужна сложная постобработка. Например, есть
  процедура для генерации сложного отчёта и его надо вывести "как есть".
- нужно обработать очень много данных без агрегации (десятки тысяч строк).
  Для этой цели эффективнее всего использовать массив с генератором
- очень ограниченный контекст использования массива (в рамках одного класса или метода)

В случаях использования массивов, их типы следует описывать в array shape формате
([пример](https://phpstan.org/writing-php-code/phpdoc-types#array-shapes))

### json ⇄ rpc params, DTO

DTO это просто способ логически сгруппировать набор полей запроса / ответа.  
Решение использовать DTO или нет принимается каждый раз при проектировании эндпоинта.  
Имена параметров метода контроллера и полей DTO используются в качестве полей json.

```php
    // пример метода контролера
    public function updateUser(
        #[RpcParam('Данные пользователя')]
        UpdateUserRequest $user,
        #[RpcParam('Нужна синхронизация в 1С')]
        bool $syncToOneC = false,
    ): UserResponse
    ...
    readonly class UpdateUserRequest {
        public function __construct(
            #[RpcParam('id пользователя')] public int $id,
            #[RpcParam('имя пользователя')] public string $name,
        ) {
        }
    }
    ...
    readonly class UserResponse {
        public function __construct(
            public int $id,
            public string $name,
            public int $departamentId,
        ) {
        }
    }
    ...
    // json:
    >>> {user: {id: 42, name: 'Борис'}, syncToOneC: false}
    <<< {id: 42, name: 'Борис', departamentId: 84}
```

**Валидация**: на rpc параметры и поля DTO можно через аттрибуты добавлять правила валидации Symfony,
они будут проверятся автоматически

```php
public function __invoke(
    #[RpcParam('Старый пароль')]
    #[Assert\NotBlank]
    string $old,

    #[RpcParam('Новый пароль')]
    #[Assert\NotBlank(message: 'Пароль не может быть пустым')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Длина пароля должна быть не менее {{ limit }} символов'
    )]
    string $new,
): void {
```

### DTO ⇄ Entity

DTO на входе имеет постфикс Request, на выходе - Response.

Request в Entity, как правило, не требуется преобразовывать - можно просто прокидывать дальше.
Request следует помечать как `readonly`, чтобы избегать проблем с мутабельностью.

Преобразование Entity в Response в простейшем случае можно делать так:

```php
    $userDto = UserResponse::build(...$userEntity->toArray());
```

В частных случаях, можно добавлять дополнительные методы в DTO:

```php
    UserResponse::fromUserAndDepart(User $user, Depertment $depart): self;
```

В обоих случаях, рекомендуется использовать именованные конструкторы вместо
`__construct` (его лучше сделать приватным)

### Частный случай - поиск

> **TODO**: FindRequest / FindResponse

### Entity ⇄ БД

Основная информация по работе в БД - в документации пакета `portal/database`.

Кратко:

- внутри Repository работаем с CpConnection (результат запроса на чтение как правило генератор с массивом внутри)
- вне Repository, за редкими исключениями, работаем с Entity
- разделяем запись и чтение на разные классы (Query / Command)

### Даты

В php коде на всех уровнях мы работаем c объектами `DateTimeImmutable`.  
При преобразовании в json используется rfc 3339 (используется в конструкторе Date в php и js).  
При работе с БД также используется rfc 3339.

> Если поле Entity / DTO не определено как DateTimeImmutable или DateTime, преобразование нужно будет сделать вручную!

### Перечисления

Есть поддержка BackedEnum. В целом, работает как с датами, но есть 2 отличия в работе с json:
- при получении, можно использовать как значение, так и название

```php
    enum GroupType: int
    {
        case GROUP = 1;
        case CATEGORY = 2;
    }
    
    // эквивалентно
    >>> {groupType: 1}
    >>> {groupType: 'GROUP'}
```

- при отправке, перечисление становится объектом вида `{name: string, value: int|string, ?title: string}`

```
    <<< {groupType: {name: 'GROUP', value: 1}}
```

Чтобы перечисление содержало `title`, enum должен имплементировать `App\Domain\Portal\Titleable`