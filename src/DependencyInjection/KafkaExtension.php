<?php

namespace D1oxyde\KafkaBundle\DependencyInjection;

use D1oxyde\KafkaBundle\ConnectionFactory;
use D1oxyde\KafkaBundle\Consumer;
use D1oxyde\KafkaBundle\DependencyInjection\Registry\ContainerProcessorRegistry;
use D1oxyde\KafkaBundle\Logger;
use D1oxyde\KafkaBundle\Producer;
use Enqueue\RdKafka\JsonSerializer;
use Enqueue\RdKafka\RdKafkaContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class KafkaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config as $name => $params) {
            $connectionFactoryId = $this->connectionFactoryRegister($container, $params, $name);

            $container->register($contextId = sprintf('kafka.%s.context', $name), RdKafkaContext::class)
                ->setFactory([new Reference($connectionFactoryId), 'createContext']);

            $container->register($consumerId = sprintf('kafka.%s.consumer', $name), Consumer::class)
                ->addArgument(new Reference($contextId))
                ->addArgument(null);

            $container->register($producerId = sprintf('kafka.%s.producer', $name), Producer::class)
                ->addArgument(new Reference($contextId));

            $this->addServiceToLocator($container, $connectionFactoryId);
            $this->addServiceToLocator($container, $contextId);
            $this->addServiceToLocator($container, $consumerId);
            $this->addServiceToLocator($container, $producerId);
        }

        $container->register('kafka.processor_registry', ContainerProcessorRegistry::class);

        $this->addServiceToLocator($container, 'kafka.processor_registry');
    }

    private function connectionFactoryRegister(ContainerBuilder $container, array $params, string $name): string
    {
        $container->register($serializerId = sprintf('kafka.%s.serializer', $name), $params['serializer'] ?? JsonSerializer::class);

        $connectionFactoryBuilder = $container->register($factoryId = sprintf('kafka.%s.connection_factory', $name), ConnectionFactory::class);

        if (isset($params['configuration']['factory'])) {
            $container->register($configurationFactoryId = sprintf('kafka.%s.configuration_factory', $name), $params['configuration']['factory'])->setAutowired(true);
            $connectionFactoryBuilder->addArgument(new Reference($configurationFactoryId));
        } else {
            $connectionFactoryBuilder->addArgument($params['configuration']);
        }

        $connectionFactoryBuilder->addArgument(new Reference($serializerId));

        if (isset($params['logger'])) {
            $logger = $params['logger'];

            if (!isset(class_implements($logger)[Logger::class])) {
                throw new \LogicException(sprintf('The "%s" connection logger must implement the interface %s', $name, Logger::class));
            }

            $container->register($loggerId = sprintf('kafka.%s.logger', $name), $logger)->setAutowired(true);

            $connectionFactoryBuilder->addArgument(new Reference($loggerId));
        }

        return $factoryId;
    }

    private function addServiceToLocator(ContainerBuilder $container, string $serviceName): void
    {
        $locatorId = 'kafka.locator';

        if ($container->hasDefinition($locatorId)) {
            $locator = $container->getDefinition($locatorId);

            $map = $locator->getArgument(0);
            $map[$serviceName] = new Reference($serviceName);

            $locator->replaceArgument(0, $map);
        }
    }
}
