<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EntityDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector to collect loaded entities.
 */
class EntityDataCollector extends DataCollector {

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * Registers loaded entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   The loaded entities.
   * @param string $entity_type
   *   The entity type of the loaded entities.
   */
  public function addLoadedEntities(array $entities, $entity_type) {
    $this->data[$entity_type] = array_keys($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'entity';
  }

  /**
   * Twig callback to return the loaded entity IDs.
   */
  public function entityIds() {
    return $this->data;
  }

} 
