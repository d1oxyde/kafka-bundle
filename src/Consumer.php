<?php

namespace D1oxyde\KafkaBundle;

use Enqueue\RdKafka\RdKafkaContext;
use LogicException;

class Consumer
{
    /**
     * @var RdKafkaContext
     */
    private $context;

    /**
     * @var Processor|null
     */
    private $processor;

    /**
     * Consumer constructor.
     * @param RdKafkaContext $context
     * @param Processor|null $processor
     */
    public function __construct(RdKafkaContext $context, Processor $processor = null)
    {
        $this->context = $context;

        if ($processor) {
            $this->setProcessor($processor);
        }
    }

    /**
     * @param Processor $processor
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @throws LogicException
     */
    public function consume(): void
    {
        $topic = $this->context->createTopic($this->processor->getTopicName());

        $consumer = $this->context->createConsumer($topic);

        while (true) {
            $message = $consumer->receive();

            $result = $this->processor->process($message, $this->context);

            switch ($result) {
                case Processor::ACK:
                    $consumer->acknowledge($message);
                    break;

                case Processor::REJECT:
                    $consumer->reject($message, false);
                    break;

                case Processor::REQUEUE:
                    $consumer->reject($message, true);
                    break;

                case Processor::ALREADY_ACKNOWLEDGED:
                    break;

                default:
                    throw new LogicException(sprintf('Status is not supported: %s', $result));
            }
        }
    }
}
