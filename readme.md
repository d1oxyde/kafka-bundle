# Symfony Kafka-bundle

## Установка

#### Создать файл конфигурации в вашем приложении
```yaml
# config/packages/kafka.yaml
kafka:
  __client_name__:
    ## configuration: Acme\Configuration
    configuration:
      global:
        group.id: 'some-group'
        metadata.broker.list: 'kafka:9092'
        enable.auto.commit: 'true'
      topic:
        auto.offset.reset: latest
    serializer: Enqueue\RdKafka\JsonSerializer
    logger: Acme\Logger
```
Где `__client_name__` — имя вашего клиента, измение его на любой другой. 
Оно испольется для указания подключения при подписки на топик кафки, при вызове команды `./bin/console kafka:consume`.

В поле `configuration` описывается конфигурация для соединения с кафкой. 
Подробнее об конфигурации можно узнать тут -> https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md. 
Также, вместо параметров, можно передать класс, который должен имплементировать интерфейс `D1oxyde\KafkaBundle\Configuration`.
Он полезен в том случае, если, например, необходимо динамичски 
вычислять значения или получать данные подключения из внешних систем. 

В `serializer` передаётся класс, который десериализирует пришедшее сообщение из топика, и сериализует при отправке в топик. 
Он должен имплементировать интерфейс `Enqueue\RdKafka\Serializer`.  

В `logger` передаётся класс, который должен имплементировать интерфейс `D1oxyde\KafkaBundle\Logger`. 
Он логгирует ошибки и успешную доставку сообщения.  

#### Подключить бандл к проекту (`config/bundles.php`):
```php
<?php

return [
    /* ... */
    D1oxyde\KafkaBundle\KafkaBundle::class => ['all' => true],
];

```

## Реализация

#### Процессор

Для подписки на топик кафки необходимо создать процессор (класс) и имплементировать интерфейс `D1oxyde\KafkaBundle\Processor`:
```php
<?php

use D1oxyde\KafkaBundle\Processor;
use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\RdKafkaMessage;

class SomeProcessor implements Processor
{
    public function process(RdKafkaMessage $message, RdKafkaContext $context): string
    {
        echo $message->getBody() . PHP_EOL;

        return self::ACK;
    }

    public function getTopicName(): string
    {
        return 'events';
    }

    public static function getProcessorName(): string
    {
        return 'some-processor';
    }
}
```
В метод `process` первым аргументом передаётся объект сообщеня из кафки, вторым аргументом передаётся контекст (соединение с кафкой). 
В свою очередь он должен возвращать `self::ACK`, `self::REJECT` или `self::REQUEUE`.

Метод `getTopicName` возвращает название топика, на который подписан процессор.

Статический метод `getProcessorName` возвращает название самого процессора.

#### Регистрация процессора

```yaml
# config/services.yaml
services:
  Acme\Kafka\SomeProcessor:
    tags:
      - { name: 'kafka.processor' }
```

#### Продюсер

Продюсер доступен через тэг `kafka.internal.producer`, реализация - класс `D1oxyde\KafkaBundle\Producer`. Для отправки
сообщений в кафку нужно вызвать метод `produce` где передать набор сообщений `RdKafkaMessage` и название топика

## Запуск

```shell script
./bin/console kafka:consume client-name processor-name
```

Первым аргументом передаётся название подключения, которое задаётся в файле конфигурации, 
вторым аргументом название процессора, определяемое в методе `getProcessorName` процессора.
