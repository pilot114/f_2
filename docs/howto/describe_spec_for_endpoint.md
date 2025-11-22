# Как добавить спецификацию для эндпоинта

В общем случае, спецификация для эндпоинта создается автоматически на основе
рефлексии кода.  
В dev режиме это происходит при каждом запросе, в prod режиме
только при обновлении кода.

### Использование phpDoc

Типовой контролер использует типы параметров и возвращаемого значения
по умолчанию, но иногда их бывает не достаточно, поэтому можно использовать phpDoc
для уточнения возвращаемых данных.

1. Описание через `array shapes`

```php
    /**
     * @return array{
     *     items: array<array{
     *        id: int,
     *        name: string
     *     }>,
     *     total: int
     * }
     */
    public function getAvailablePartners(): array
    {
        return $this->useCase->getUserList()->toArray();
    }
```

2. Описание дженерика (эквивалентно примеру выше)

```php
    /**
     * @return FindResponse<FindItemResponse>
     */
    public function getAvailablePartners(): FindResponse
    {
        return $this->useCase->getUserList()->toFindResponse();
    }
```