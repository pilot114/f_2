## Файловая структура

В корне проекта структура стандартная для Symfony проектов.

В `src`:

    src
        Domain
            Domain1
                SubDomain1
                SubDomain2
            Domain2
                SubDomain3
                SubDomain4
        Gateway
        System

`Domain` содержит весь бизнес-код портала. Домены следует именовать в соответствии
с департаментами компании, поддомены - в соответствии с отдельными сервисами.

В `Gateway` находятся интерфейсы, внедряемые в юзкейсы.  
В `System` находится инфраструктурный код.

Типовая структура поддомена:

    Example
        Controller
        UseCase
        Entity
        Enum
        Repository
        DTO
        Service
        Event
        Listener

`Controller, UseCase, Entity, Repository` - см. [Архитектура](../arch/common.md)  

`DTO` используются чтобы сгруппировать некоторый набор данных при их получении или отправке
в контроллере.  
`Service` это расширение функционала имплементации одного из `Gateway`. Его удобно использовать,
когда требуется вынести некоторую специфичную бизнес-логику из юзкейса, например -
формирование письма или excel-файла.  
`Event` и `Listener` используются, чтобы организовать взаимодействие между доменами
с [минимальным сцеплением](https://en.wikipedia.org/wiki/Coupling_(computer_programming))

## Префиксы и постфиксы

`entity` не имеет требований к названию.  
Для `enum` рекомендуется использование постфикса `Type`

    Entity.php
    UserStatusType.php

Контроллер имеет постфикс `Controller`. Контроллеры для чтения данных надо  
начинать с `Get` или `Find`, для записи - с любого другого подходящего глагола

    GetEntityByNameController.php

Имя rpc метода: `domain.subdomain.action`

    #[RpcMethod('test.example.getEntityByName')

DTO получаемые в запросе имеют постфикс `Request`, возвращаемые - `Response`.
Название DTO может соотноситься как с сущностью (в ответе), так и с некоторым действием (в запросе).

    CreateEntityRequest.php
    UpdateEntityRequest.php
    EntityResponse.php

Нейминг юзкейса аналогичен контроллеру, но с постфиксом `UseCase`

    GetEntityByNameUseCase.php

Репозитории называются по имени основной сущности, с которой работают.
Репозиторий для чтения данных имеет постфикс `QueryRepository`, для записи - `CommandRepository`
Названия репозиториев, которые служат заглушкой и не ходят в БД, следует начинать с `Fake`

    EntityQueryRepository.php
    EntityCommandRepository.php
    FakeEntityCommandRepository.php

Сервисы берут названия от поддомена и gateway, которые связывают:

    ExampleMailer.php

События имеют постфикс `Event` и сообщают о некотором действии с сущностью.
Листенеры имеют постфикс `Listener` и сообщают о реакции на событие.

    CreateEntityEvent.php
    EntityLoggingListener.php

Сообщения имеют постфикс `Message`.
Обработчики сообщений имеют постфикс `MessageHandler`.

    TestMessage.php
    TestMessageHandler.php

## Прочий нейминг

Эти главы "Совершенного кода" определенно стоят того, чтобы их перечитать

    6.2 Имена классов
    7.3 Удачные имена методов
    11 Сила имен переменных