<?php

namespace Core\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('admin');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
//        $rootNode
//          ->children()
//            ->scalarNode('google_application_name')->isRequired()->cannotBeEmpty()->end()
//            ->scalarNode('google_oauth2_client_id')->isRequired()->cannotBeEmpty()->end()
//            ->scalarNode('google_oauth2_client_secret')->isRequired()->cannotBeEmpty()->end()
//            ->scalarNode('google_oauth2_redirect_uri')->isRequired()->cannotBeEmpty()->end()
//            ->scalarNode('google_developer_key')->isRequired()->cannotBeEmpty()->end()
//            ->scalarNode('google_site_name')->isRequired()->cannotBeEmpty()->end()
//
//            ->scalarNode('authClass')->end()
//            ->scalarNode('ioClass')->end()
//            ->scalarNode('cacheClass')->end()
//            ->scalarNode('basePath')->end()
//            ->scalarNode('ioFileCache_directory')->end()
//          //end rootnode children
//          ->end();

        //let use the api defaults
        //$this->addServicesSection($rootNode);
//
        return $treeBuilder;
    }
}
