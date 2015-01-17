<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Compiler\EventPass.
 */

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EventPass.
 */
class EventPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $definition = $container->findDefinition('http_kernel.basic');
    $definition->replaceArgument(1, new Reference('webprofiler.debug.controller_resolver'));

    // Replace the regular event_dispatcher service with the debug one.
    $definition = $container->findDefinition('event_dispatcher');
    $definition->setPublic(FALSE);
    $container->setDefinition('webprofiler.debug.event_dispatcher.parent', $definition);
    $container->setAlias('event_dispatcher', 'webprofiler.debug.event_dispatcher');
  }

}
