## Установка

При использовании [docker-сборки для проектов кор.портала](http://192.168.6.29/portal/docker),
клонирование проекта и установка зависимостей происходит
автоматически при запуске `make update && make start`.

> **Важно**: конфиденциальные данные (ключи / токены доступа) не хранятся под гитом.  
При первоначальной установке нужно создать файлы `.env.local` и `.env.dev.local`,  
> затем скопировать в них переменные с уже развернутой системы (попросить у коллег  
> или взять с тестового сервера)
> 
> Также, при наличии доступа, эти переменные можно получить из GitLab  
> (portal/back2 > Settings > CI/CD > Variables)

### Архитектура

- [Общая информация](./docs/architecture/common.md)
- [Интеграция](./docs/architecture/integrate.md)
- [Переменные окружения](./docs/architecture/envs.md)
- [Представление данных](./docs/architecture/data.md)
- [Исключения](./docs/architecture/exceptions.md)
- [Прочее](./docs/architecture/faq.md)
- ADR (Architecture Decision Records):
  - [001 - back2](./docs/architecture/adr/2024-01-24-001-back2.md)
  - [002 - fix structure](./docs/architecture/adr/2025-10-03-002-fix-structure.md)
  - [003 - openrpc client](./docs/architecture/adr/2025-10-08-003-openrpc-client.md)

### Руководства

- [Руководство по проектированию](./docs/howto/design_a_new_service.md)
- [Руководство по проектированию с помощью LLM](./docs/howto/design_a_new_service_llm.md)
- [Как добавить спецификацию для эндпоинта](./docs/howto/describe_spec_for_endpoint.md)
- [Рефакторинг сервиса с помощью LLM](./docs/howto/refactor_service_llm.md)

### Компоненты

- [Апи](./docs/components/api.md)
- [Mocks для апи](./docs/components/api_mock.md)
- [Общие компоненты](./docs/components/common.md)
- [Скачивание excel-файлов](./docs/components/excel.md)
- [Работа с файлами](./docs/components/files.md)
- [MCP сервер](./docs/components/MCP.md)
- [Права и роли](./docs/components/permissions.md)
- [Очереди](./docs/components/queue.md)
- [Инфраструктурный слой](./docs/components/system.md)
- [Тестирование](./docs/components/testing.md)
- [Воркеры](./docs/components/workers.md)

### Соглашения

- [Соглашения по именованию в коде](./docs/conventions/naming.md)
- [Соглашения по именованию коммитов](./docs/conventions/commits.md)
- [Соглашения по обновлению](./docs/conventions/update.md)

### Дополнительная документация

[Symfony](https://symfony.com/doc/current/index.html)  
[PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/stable/)  

## Локальные инструменты отладки

[профайлер](http://local.portal.com/?SPX_KEY=dev&SPX_UI_URI=/) - для просмотра колстэка запросов.

Для работы профайлера в Postman, нужно установить заголовок:

    Cookie: SPX_ENABLED=1; SPX_KEY=dev

Для работы профайлера в консоли нужно установить переменную окружения:

    SPX_ENABLED=1 bin/console

[mailhog](http://local.portal.com:8025/#) - для просмотра писем. Mailhog перехватывает все письма, отправленные локально.

## Ведение документации

- в документации должно быть зафикисировано поведение инфраструктурного кода
  (как решать типовые задачи, проверять права, писать тесты и т.д.)
- документацию надо периодически обновлять
- бизнес код тоже нужно описывать, но на самом высоком уровне (домены / поддомены)
- внешние ссылки допускаются только на статичный и общедоступный контент.
  Примеры плохих ссылок: макеты в figma, google docs, ресурсы с ограниченным доступом.
