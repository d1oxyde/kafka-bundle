<?php

namespace D1oxyde\KafkaBundle;

use Enqueue\RdKafka\RdKafkaContext;
use Enqueue\RdKafka\RdKafkaMessage;

interface Processor
{
    const ACK = 'kafka.ack';

    const REJECT = 'kafka.reject';

    const REQUEUE = 'kafka.requeue';

    const ALREADY_ACKNOWLEDGED = 'kafka.already_acknowledged';

    public function process(RdKafkaMessage $message, RdKafkaContext $context): string;

    public function getTopicName(): string;

    public static function getProcessorName(): string;
}