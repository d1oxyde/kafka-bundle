<?php

namespace D1oxyde\KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('kafka');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('key')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('configuration')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return ['factory' => $v]; })
                        ->end()
                        ->variablePrototype()->end()
                    ->end()
                    ->scalarNode('serializer')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('logger')
                        ->defaultNull()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}