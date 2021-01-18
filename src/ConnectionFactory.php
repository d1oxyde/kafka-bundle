<?php

namespace D1oxyde\KafkaBundle;

use RdKafka\{Kafka, Message};
use Enqueue\RdKafka\JsonSerializer;
use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\Serializer;

/**
 * Class ConnectionFactory
 * @package D1oxyde\KafkaBundle
 */
class ConnectionFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var JsonSerializer|Serializer|null
     */
    private $serializer;

    /**
     * ConnectionFactory constructor.
     * @param $arrayConfigOrFactory
     * @param Serializer|null $serializer
     * @param Logger|null $logger
     */
    public function __construct($arrayConfigOrFactory, Serializer $serializer = null, Logger $logger = null)
    {
        $this->setConfig($arrayConfigOrFactory);

        if ($logger) {
            $this->addLoggerToConfig($logger);
        }

        $this->serializer = $serializer ?: new JsonSerializer();
    }

    /**
     * @return RdKafkaContext
     */
    public function createContext(): RdKafkaContext
    {
        $context = new RdKafkaContext($this->config);
        $context->setSerializer($this->serializer);

        return $context;
    }

    /**
     * @return array[]
     */
    private function defaultConfig(): array
    {
        return [
            'global' => [
                'group.id' => uniqid('', true),
                'metadata.broker.list' => 'localhost:9092',
            ],
        ];
    }

    /**
     * @param $arrayConfigOrFactory
     */
    private function setConfig($arrayConfigOrFactory): void
    {
        $config = $arrayConfigOrFactory instanceof Configuration
            ? $arrayConfigOrFactory->getConfiguration()
            : $arrayConfigOrFactory;

        $this->config = array_replace_recursive($this->defaultConfig(), $config);
    }

    /**
     * @param Logger $logger
     */
    private function addLoggerToConfig(Logger $logger): void
    {
        $this->config = array_replace_recursive(
            $this->config,
            [
                'dr_msg_cb' => function (Kafka $kafka, Message $message) use ($logger) {
                    $logger->deliveryReportMessage($kafka, $message);
                },
                'error_cb' => function ($kafka, $err, $reason) use ($logger) {
                    $logger->error($kafka, $err, $reason);
                },
            ]
        );
    }
}