### Асинхронное выполнение отчётов

Отчёты выполняются в асинхронной очереди `reporter_export`. После выполнения отчёта пользователь получает письмо с прикреплённым Excel-файлом.

## Архитектура

### Компоненты

1. **ExportReportMessage** (`src/Domain/Dit/Reporter/Message/ExportReportMessage.php`)
   - Сообщение для очереди
   - Содержит: reportId, input (параметры отчёта), userId, userEmail

2. **ExportReportMessageHandler** (`src/Domain/Dit/Reporter/Message/ExportReportMessageHandler.php`)
   - Обработчик сообщений из очереди
   - Выполняет отчёт и отправляет результат на email

3. **ReporterEmailer** (`src/Domain/Dit/Reporter/Service/ReporterEmailer.php`)
   - Сервис для отправки писем с готовыми отчётами
   - Прикрепляет Excel-файл к письму

4. **ExportReportAsyncController** (`src/Domain/Dit/Reporter/Controller/ExportReportAsyncController.php`)
   - Контроллер для RPC-метода `dit.reporter.exportReportAsync`
   - Ставит задачу в очередь для асинхронного выполнения

5. **ExportReportController** (`src/Domain/Dit/Reporter/Controller/ExportReportController.php`)
   - Контроллер для RPC-метода `dit.reporter.exportReport`
   - Выполняет отчёт синхронно и возвращает файл сразу

## Использование

### Запуск воркера

```bash
# Запуск воркера для обработки отчётов
bin/console messenger:consume reporter_export -vv
```

### Отправка отчёта в очередь через RPC

**Асинхронный режим (с отправкой на email):**
```json
{
  "jsonrpc": "2.0",
  "method": "dit.reporter.exportReportAsync",
  "params": {
    "id": 9012,
    "input": {
      "rc": 45,
      "ds": "01.06.2025",
      "de": "19.06.2025"
    }
  },
  "id": 1
}
```

Ответ:
```json
{
  "success": true,
  "message": "Отчёт поставлен в очередь на формирование. Результат будет отправлен на user@example.com"
}
```

**Синхронный режим (немедленная выгрузка файла):**
```json
{
  "jsonrpc": "2.0",
  "method": "dit.reporter.exportReport",
  "params": {
    "id": 9012,
    "input": {
      "rc": 45,
      "ds": "01.06.2025",
      "de": "19.06.2025"
    }
  },
  "id": 1
}
```

Ответ: FileResponse с файлом отчёта

### Тестирование через CLI

```bash
# Тестовая отправка отчёта в очередь
bin/console reporter:test-export <reportId> <userId> <userEmail>

# Пример
bin/console reporter:test-export 9012 123 test@example.com
```

### Мониторинг очереди

```bash
# Проверить количество сообщений в очередях
bin/console messenger:stats

# Просмотреть неудавшиеся сообщения
bin/console messenger:failed:show

# Повторить неудавшиеся сообщения
bin/console messenger:failed:retry
```

## Обработка ошибок

При возникновении ошибок во время выполнения отчёта:
- Ошибка логируется в системный лог
- Сообщение может быть обработано повторно (retry)
- Пользователь не получит письмо при ошибке

## Конфигурация

Настройки очереди находятся в `config/packages/messenger.yaml`:

```yaml
framework:
    messenger:
        transports:
            reporter_export:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    stream: 'reporter_export'
                    group: 'workers'
                    auto_setup: true
                    delete_after_ack: true

        routing:
            'App\Domain\Dit\Reporter\Message\ExportReportMessage': reporter_export
```

## Особенности

- Отчёты выполняются с параметром `allData: true`, т.е. без лимита на количество строк
- В обработчике установлен `ini_set('memory_limit', -1)` не требуется, т.к. это CLI-процесс
- Для каждого отчёта проверяются права доступа пользователя
- Email отправляется с адреса `bot@sibvaleo.com`
- Excel файл создаётся во временной директории и удаляется после отправки
