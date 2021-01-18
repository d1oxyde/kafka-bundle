<?php

namespace D1oxyde\KafkaBundle;

use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\RdKafkaMessage;
use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;

/**
 * Class Producer
 *
 * @package D1oxyde\KafkaBundle
 */
class Producer
{
    /**
     * @var RdKafkaContext
     */
    private $context;

    /**
     * Consumer constructor.
     *
     * @param RdKafkaContext $context
     */
    public function __construct(RdKafkaContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param RdKafkaMessage[] $messages
     *
     * @param string           $topicName
     *
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function produce(array $messages, string $topicName)
    {
        $producer = $this->context->createProducer();
        $topic = $this->context->createTopic($topicName);
        foreach ($messages as $message) {
            $producer->send($topic, $message);
        }
    }
}
