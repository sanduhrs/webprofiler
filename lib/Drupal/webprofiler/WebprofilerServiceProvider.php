<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerServiceProvider.
 */

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the webprofiler module.
 */
class WebprofilerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Add a compiler pass to discover all data collector services.
    $container->addCompilerPass(new ProfilerPass());

    // Replace the existing state service with a wrapper to collect the
    // requested data.
    $container->setDefinition('state.default', $container->getDefinition('state'));
    $container->register('state', 'Drupal\webprofiler\DataCollector\StateDataCollector')
      ->addArgument(new Reference(('state.default')))
      ->addTag('data_collector', array(
        'template' => '@webprofiler/Collector/state.html.twig',
        'id' => 'state',
        'priority' => -10
      ));

    // Replaces the existing cache_factory service to be able to collect the
    // requested data.
    $container->setDefinition('cache_factory.default', $container->getDefinition('cache_factory'));
    $container->register('cache_factory', 'Drupal\webprofiler\Cache\CacheFactoryWrapper')
      ->addArgument(new Reference('cache_factory.default'))
      ->addArgument(new Reference('webprofiler.cache'));

    // Replaces the existing form_builder service to be able to collect the
    // requested data.
    $container->setDefinition('form_builder.default', $container->getDefinition('form_builder'));
    $container->register('form_builder', 'Drupal\webprofiler\Form\FormBuilderWrapper')
      ->addArgument(new Reference('module_handler'))
      ->addArgument(new Reference('keyvalue.expirable'))
      ->addArgument(new Reference('event_dispatcher'))
      ->addArgument(new Reference('url_generator'))
      ->addArgument(new Reference('string_translation'))
      ->addArgument(new Reference('csrf_token', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
      ->addArgument(new Reference('http_kernel', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
      ->addMethodCall('setRequest', array(new Reference('request', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)));

    // Replace the existing config.factory service with a wrapper to collect the
    // requested configs.
    $container->setDefinition('config.factory.default', $container->getDefinition('config.factory'));
    $container->register('config.factory', 'Drupal\webprofiler\Config\ConfigFactoryWrapper')
      ->addArgument(new Reference('webprofiler.config'))
      ->addArgument(new Reference('config.factory.default'));

    // Replaces the event dispatcher passed into the http kernel.
    $definition = $container->findDefinition('http_kernel');
    $arguments = $definition->getArguments();
    $arguments[0] = new Reference('webprofiler.debug.event_dispatcher');
    $arguments[2] = new Reference('webprofiler.debug.controller_resolver');
    $definition->setArguments($arguments);

    // Register an additional twig extension.
    $container->getDefinition('twig')
      ->addMethodCall('addExtension', array(new Reference('webprofiler.twig_extension')));

  }

}

