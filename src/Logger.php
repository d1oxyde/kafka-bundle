<?php

namespace D1oxyde\KafkaBundle;

use RdKafka\Kafka;
use RdKafka\Message;

interface Logger
{
    public function error($kafka, $err, $reason): void;

    public function deliveryReportMessage(Kafka $kafka, Message $message): void;
}