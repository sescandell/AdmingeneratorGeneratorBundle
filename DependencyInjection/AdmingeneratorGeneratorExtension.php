<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

use Admingenerator\GeneratorBundle\Exception\ModelManagerNotSelectedException;

class AdmingeneratorGeneratorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        // Fix template_dirs
        $doctrine_template_dirs = $doctrineodm_template_dirs = $propel_template_dirs = array();
        foreach ($config['templates_dirs'] as $dir) {
            $doctrine_template_dirs[]    = $dir.'/Doctrine';
            $doctrineodm_template_dirs[] = $dir.'/DoctrineODM';
            $propel_template_dirs[]      = $dir.'/Propel';
        }

        if ($config['use_doctrine_orm']) {
            $loader->load('doctrine_orm.xml');
            $container->setParameter('admingenerator.doctrine_templates_dirs', $doctrine_template_dirs);
        }
        
        if ($config['use_doctrine_odm']) {
            $loader->load('doctrine_odm.xml');
            $container->setParameter('admingenerator.doctrineodm_templates_dirs', $doctrineodm_template_dirs);
        }
        
        if ($config['use_propel']) {
            $loader->load('propel.xml');
            $container->setParameter('admingenerator.propel_templates_dirs', $propel_template_dirs);
        }
        
        if (!($config['use_doctrine_orm'] || $config['use_doctrine_odm'] || $config['use_propel'])) {
            throw new ModelManagerNotSelectedException();
        }

        $container->setParameter('admingenerator.overwrite_if_exists', $config['overwrite_if_exists']);
        $container->setParameter('admingenerator.base_admin_template', $config['base_admin_template']);
        $container->setParameter('admingenerator.dashboard_welcome_path', $config['dashboard_welcome_path']);
        $container->setParameter('admingenerator.login_path', $config['login_path']);
        $container->setParameter('admingenerator.logout_path', $config['logout_path']);
        $container->setParameter('admingenerator.exit_path', $config['exit_path']);        
        $container->setParameter('admingenerator.components_dir', $config['components_dir']);
        $container->setParameter('admingenerator.stylesheets', $config['stylesheets']);
        $container->setParameter('admingenerator.javascripts', $config['javascripts']);

        $date_type = array(
            'class' => 'Admingenerator\GeneratorBundle\Form\Type\DateType',
            'tags' => array('name' => 'form.type', 'alias' => 'date'),
        );
        
        $container->setParameter('services.form.type.date', $date_type);

        $container->setParameter('admingenerator.twig', $config['twig']);

        if ($config['twig']['use_localized_date']) {
            // Register Intl extension for localized date
            $container->register('twig.extension.intl', 'Twig_Extensions_Extension_Intl')
                        ->addTag('twig.extension');
        }

    }

    public function getAlias()
    {
        return 'admingenerator_generator';
    }
}
