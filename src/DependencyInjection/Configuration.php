<?php

namespace Vskstudio\Takt\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tb = new TreeBuilder('takt');
        $tb->getRootNode()
            ->children()
                ->scalarNode('domain')->defaultValue('')->end()
                ->scalarNode('endpoint')->defaultValue('https://takt.example.com')->end()
                ->scalarNode('script_origin')
                    ->info('First-party origin to serve the tracker + derive the endpoint from ({origin}/api/event) — your Takt domain or a custom domain to dodge ad-blockers (endpoint wins over it).')
                    ->defaultNull()
                ->end()
                ->scalarNode('api_key')->defaultNull()->end()
                ->scalarNode('mode')->defaultValue('inline')->end()
                ->booleanNode('outbound')->defaultFalse()->end()
                ->booleanNode('files')->defaultFalse()->end()
                ->booleanNode('exclude_localhost')->defaultTrue()->end()
            ->end();

        return $tb;
    }
}
