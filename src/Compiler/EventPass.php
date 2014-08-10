<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Compiler\EventPass.
 */

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EventPass
 */
class EventPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $definition = $container->findDefinition('http_kernel');
    $definition->replaceArgument(1, new Reference('webprofiler.debug.controller_resolver'));

    // replace the regular event_dispatcher service with the traceable one.
    $definition = $container->findDefinition('event_dispatcher');
    $container->setDefinition('webprofiler.debug.event_dispatcher.parent', $definition);

    $definition = $container->findDefinition('webprofiler.debug.event_dispatcher');
    $container->setDefinition('event_dispatcher', $definition);
  }

}
