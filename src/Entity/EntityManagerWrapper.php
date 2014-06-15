<?php

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Entity\EntityManager;

class EntityManagerWrapper extends EntityManager {

  /**
   * @var array[EntityStorageInterface]
   */
  private $storages;

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type) {
    $controller = $this->getController($entity_type, 'storage', 'getStorageClass');

    if ('block' == $entity_type) {
      $decorator = new BlockStorageDecorator($controller);
      $this->storages[] = $decorator;

      return $decorator;
    }

    return $controller;
  }

  /**
   * @return mixed
   */
  public function getStorages() {
    return $this->storages;
  }

}
