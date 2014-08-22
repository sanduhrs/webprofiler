<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Compiler\EventPass.
 */

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EntityPass
 */
class EntityPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    // replace the regular entity.manager service with the traceable one.
    $definition = $container->findDefinition('entity.manager');
    $definition->setPublic(FALSE);
    $container->setDefinition('webprofiler.debug.entity.manager.parent', $definition);
    $container->setAlias('entity.manager', 'webprofiler.debug.entity.manager');
  }

}
