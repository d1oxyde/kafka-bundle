<?php

namespace D1oxyde\KafkaBundle\Command;

use D1oxyde\KafkaBundle\Consumer;
use D1oxyde\KafkaBundle\DependencyInjection\Registry\ContainerProcessorRegistry;
use D1oxyde\KafkaBundle\Processor;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends Command
{
    protected static $defaultName = 'kafka:consume';

    private $container;

    private $consumerIdPattern;

    public function __construct(ContainerInterface $container, string $consumerIdPattern = 'kafka.%s.consumer')
    {
        $this->container = $container;
        $this->consumerIdPattern = $consumerIdPattern;

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->addArgument('client', InputArgument::REQUIRED, 'The client to consume messages from.')
            ->addArgument('processor', InputArgument::REQUIRED, 'The processor for processing events.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = $this->getConsumer($input->getArgument('client'));
        $consumer->setProcessor($this->getProcessor($input->getArgument('processor')));
        $consumer->consume();

        return 0;
    }

    private function getConsumer(string $name): Consumer
    {
        return $this->container->get(sprintf($this->consumerIdPattern, $name));
    }

    private function getProcessorRegistry(): ContainerProcessorRegistry
    {
        return $this->container->get('kafka.processor_registry');
    }

    private function getProcessor(string $name): Processor
    {
        return $this->getProcessorRegistry()->get($name);
    }
}