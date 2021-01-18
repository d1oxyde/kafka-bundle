<?php

namespace D1oxyde\KafkaBundle;

use D1oxyde\KafkaBundle\DependencyInjection\Pass\BuildProcessorRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class KafkaBundle
 * @package D1oxyde\KafkaBundle
 */
class KafkaBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new BuildProcessorRegistryPass());
    }
}