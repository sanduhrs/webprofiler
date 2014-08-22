<?php

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Entity\EntityManager;
use Drupal\webprofiler\Entity\Block\BlockStorageDecorator;
use Drupal\webprofiler\Entity\Block\BlockViewBuilderDecorator;

class EntityManagerWrapper extends EntityManager {

  /**
   * @var array[EntityStorageInterface]
   */
  private $loaded;

  /**
   * @var array[EntityViewBuilderInterface]
   */
  private $rendered;

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type) {
    $controller = $this->getHandler($entity_type, 'storage');

    if ('block' == $entity_type) {
      $decorator = new BlockStorageDecorator($controller);
      $this->loaded[] = $decorator;

      return $decorator;
    }

    return $controller;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type) {
    $controller = $this->getHandler($entity_type, 'view_builder');

    if ('block' == $entity_type) {
      $decorator = new BlockViewBuilderDecorator($controller);
      $this->rendered[] = $decorator;

      return $decorator;
    }

    return $controller;
  }

  /**
   * @return array[EntityStorageInterface]
   */
  public function getLoaded() {
    return $this->loaded;
  }

  /**
   * @return array[EntityViewBuilderInterface]
   */
  public function getRendered() {
    return $this->rendered;
  }

}
