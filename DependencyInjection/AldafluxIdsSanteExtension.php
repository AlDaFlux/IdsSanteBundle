<?php

namespace Aldaflux\AldafluxIdsSanteBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class AldafluxIdsSanteExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $container->setParameter( 'aldaflux_ids_sante.application_name', $config[ 'application_name' ]);
        $container->setParameter( 'aldaflux_ids_sante.active', $config[ 'active' ]);
        $container->setParameter( 'aldaflux_ids_sante.user.class', $config['user']['class']);
        $container->setParameter( 'aldaflux_ids_sante.user.find_by', $config['user']['find_by']);
        $container->setParameter( 'aldaflux_ids_sante.prefixe', $config['prefixe']);
        $container->setParameter( 'aldaflux_ids_sante.proxy_enabled', $config['proxy']['enabled']);
        $container->setParameter( 'aldaflux_ids_sante.ip', $config['proxy']['ip']);
        $container->setParameter( 'aldaflux_ids_sante.soap.wsdl.log', $config['soap']['wsdl']['log']);
        
        
        $container->setParameter( 'aldaflux_ids_sante.api_root_url', $config['api_root_url']);
        $container->setParameter( 'aldaflux_ids_sante.application_key', $config['application_key']);
        $container->setParameter( 'aldaflux_ids_sante.token', $config['token']);
        
        $loader->load('services.yml');
                
        
        
    }
}
