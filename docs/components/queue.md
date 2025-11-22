### Очереди

Для того чтобы добавить и использовать новую очередь, нужно выполнить следующие действия:

1. Создать класс сообщения.
К этому классу нет никаких требований, это просто обёртка, призванная типизировать сообщение

```php
    readonly class TestMessage
    {
        public function __construct(
            public string $content,
        ) {
        }
    }
```
2. Добавить сообщение в роутинг (см. config/packages/messenger.yaml)
3. Зарегистрировать обработчик очереди

```php
    #[AsMessageHandler]
    readonly class TestMessageHandler
    {
        public function __invoke(TestMessage $task): void
        {
            echo $task->content . "\n";
        }
    }
```
4. Отправить сообщение в очередь

```php
    class TestMessageProducer
    {
        public function __construct(
            private MessageBusInterface $bus,
        ) {
            $this->bus->dispatch(new TestMessage($message ?: 'empty'));
        }
    }
```

### Ручное тестирование очереди

    # запуск воркера, который будет запускать соотв. обработчики сообщений
    bin/console messenger:consume test_messages -vv

    # отправить тестовое сообщение (в отдельной вкладке терминала)
    bin/console system:testMessageProducer
