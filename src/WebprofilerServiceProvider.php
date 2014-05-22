<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerServiceProvider.
 */

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\EventPass;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Drupal\webprofiler\Compiler\ViewsPass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
    $container->addCompilerPass(new EventPass(), PassConfig::TYPE_AFTER_REMOVING);
    $container->addCompilerPass(new ViewsPass(), PassConfig::TYPE_AFTER_REMOVING);

    // Replace the existing state service with a wrapper to collect the
    // requested data.
    $container->setDefinition('state.default', $container->getDefinition('state'));
    $container->register('state', 'Drupal\webprofiler\DataCollector\StateDataCollector')
      ->addArgument(new Reference(('state.default')))
      ->addTag('data_collector', array(
        'template' => '@webprofiler/Collector/state.html.twig',
        'id' => 'state',
        'title' => 'State',
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
      ->addArgument(new Reference('form_validator'))
      ->addArgument(new Reference('form_submitter'))
      ->addArgument(new Reference('module_handler'))
      ->addArgument(new Reference('keyvalue.expirable'))
      ->addArgument(new Reference('event_dispatcher'))
      ->addArgument(new Reference('request_stack'))
      ->addArgument(new Reference('csrf_token', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
      ->addArgument(new Reference('http_kernel', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));

    // Replace the existing config.factory service with a wrapper to collect the
    // requested configs.
    $container->setDefinition('config.factory.default', $container->getDefinition('config.factory'));
    $container->register('config.factory', 'Drupal\webprofiler\Config\ConfigFactoryWrapper')
      ->addArgument(new Reference('webprofiler.config'))
      ->addArgument(new Reference('config.factory.default'));
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('twig')
      ->addMethodCall('addExtension', array(new Reference('webprofiler.twig_extension')));
  }
}
