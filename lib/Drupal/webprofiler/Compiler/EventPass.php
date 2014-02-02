<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Compiler\EventPass.
 */

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $definition = $container->findDefinition('http_kernel');
    $definition->replaceArgument(2, new Reference('webprofiler.debug.controller_resolver'));

    // replace the regular event_dispatcher service with the tracable one.
    $definition = $container->findDefinition('event_dispatcher');
    $definition->setPublic(false);
    $container->setDefinition('webprofiler.debug.event_dispatcher.parent', $definition);
    $container->setAlias('event_dispatcher', 'webprofiler.debug.event_dispatcher');
  }

} 
