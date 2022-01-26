<?php

namespace Aldaflux\AldafluxIdsSanteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Symfony\Component\HttpKernel\Kernel;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aldaflux_ids_sante');

        if (Kernel::VERSION_ID >= 40200) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('aldaflux_ids_sante');
        }        
                
        
        $rootNode->children()
                ->scalarNode( 'application_name' )->defaultValue("none")->end()
                ->booleanNode( 'active' )->defaultFalse()->end()
                ->scalarNode( 'prefixe' )->defaultValue("03")->end()
                ->arrayNode( 'soap' )->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode( 'wsdl' )->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('log') ->defaultValue('http://api.idshost.priv/log.wsdl')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode( 'user' )->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class') ->defaultValue('App:User')->end()
                        ->scalarNode('find_by')->defaultValue('findOneByUsername')->end()
                    ->end()
                ->end()
                ->arrayNode( 'proxy' )->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( 'enabled' )->defaultValue(false)->end()
                        ->scalarNode( 'ip' )->defaultValue(null)->end()
                    ->end();
        
        
        return $treeBuilder;
    }


}
