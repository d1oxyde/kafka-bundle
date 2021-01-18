<?php

require __DIR__ . '/../../../../autoload.php';

use Enqueue\RdKafka\RdKafkaConnectionFactory;

$connectionFactory = new RdKafkaConnectionFactory([
    'global' => [
        'group.id' => uniqid('', true),
        'metadata.broker.list' => 'kafka:9092',
        'enable.auto.commit' => 'true',
    ],
    'topic' => [
        'auto.offset.reset' => 'latest',
    ],
]);

$context = $connectionFactory->createContext();

$events = $context->createQueue('events');

$consumer = $context->createConsumer($events);

while (true) {
    $message = $consumer->receive();

    echo $message->getBody() . PHP_EOL;

    $consumer->acknowledge($message);
}