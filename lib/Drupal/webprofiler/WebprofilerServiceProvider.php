<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerServiceProvider.
 */

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the webprofiler module.
 */
class WebprofilerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new ProfilerPass());

    // Replace the existing state service with a wrapper to collect the
    // requested data.
    $container->setDefinition('state.default', $container->getDefinition('state'));
    $container->register('state', 'Drupal\webprofiler\DataCollector\StateDataCollector')
      ->addArgument(new Reference(('state.default')))
      ->addTag('data_collector', array('template' => '@webprofiler/Collector/state.html.twig', 'id' => 'state', 'priority' => -10));

    // Replaces the existing cache_factory service to be able to collect the
    // requested data.
    $container->setDefinition('cache_factory.default', $container->getDefinition('cache_factory'));
    $container->register('cache_factory', 'Drupal\webprofiler\Cache\CacheFactoryWrapper')
      ->addArgument(new Reference('cache_factory.default'))
      ->addArgument(new Reference('webprofiler.cache'));
  }

}

