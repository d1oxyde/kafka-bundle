<?php

namespace D1oxyde\KafkaBundle\DependencyInjection\Pass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuildProcessorRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tag = 'kafka.processor';
        $map = [];

        foreach ($container->findTaggedServiceIds($tag) as $serviceId => $tagAttributes) {
            $map[$serviceId::getProcessorName()] = new Reference($serviceId);
        }

        $registry = $container->getDefinition($processorRegistryId = 'kafka.processor_registry');
        $registry->setArgument(0, ServiceLocatorTagPass::register($container, $map, $processorRegistryId));
    }
}