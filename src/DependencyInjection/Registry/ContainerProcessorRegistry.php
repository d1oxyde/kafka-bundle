<?php

namespace D1oxyde\KafkaBundle\DependencyInjection\Registry;

use D1oxyde\KafkaBundle\Processor;
use LogicException;
use Psr\Container\ContainerInterface;

class ContainerProcessorRegistry
{
    private $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public function get(string $processorName): Processor
    {
        if (false == $this->locator->has($processorName)) {
            throw new LogicException(sprintf('Service locator does not have a processor with name "%s".', $processorName));
        }

        return $this->locator->get($processorName);
    }
}