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
        'auto.offset.reset' => 'beginning',
    ],
]);

$context = $connectionFactory->createContext();
$message = $context->createMessage('HELLO');
$topic = $context->createTopic('events');

$context->createProducer()->send($topic, $message);
