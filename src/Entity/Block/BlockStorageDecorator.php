<?php

namespace Drupal\webprofiler\Entity\Block;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\webprofiler\Decorator;

/**
 * Class BlockStorageDecorator
 */
class BlockStorageDecorator extends BlockDecorator implements ConfigEntityStorageInterface {

  /**
   * @param ConfigEntityStorageInterface $controller
   */
  public function __construct(ConfigEntityStorageInterface $controller) {
    parent::__construct($controller);

    $this->blocks = array();
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $ids = NULL) {
    $this->getOriginalObject()->resetCache($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $entities = $this->getOriginalObject()->loadMultiple($ids);

    $this->blocks = array_merge($this->blocks, $entities);

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    $entity = $this->getOriginalObject()->load($id);

    $this->blocks[] = $entity;

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUnchanged($id) {
    return $this->getOriginalObject()->loadUnchanged($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadRevision($revision_id) {
    return $this->getOriginalObject()->loadRevision($revision_id);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRevision($revision_id) {
    $this->getOriginalObject()->deleteRevision($revision_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProperties(array $values = array()) {
    $entities = $this->getOriginalObject()->loadByProperties($values);

    $this->blocks = array_merge($this->blocks, $entities);

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    return $this->getOriginalObject()->create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    $this->getOriginalObject()->delete($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    return $this->getOriginalObject()->save($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryServicename() {
    return $this->getOriginalObject()->getQueryServicename();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($conjunction = 'AND') {
    return $this->getOriginalObject()->getQuery($conjunction);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return $this->getOriginalObject()->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->getOriginalObject()->getEntityType();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigPrefix() {
    return $this->getOriginalObject()->getConfigPrefix();
  }

  /**
   * {@inheritdoc}
   */
  public static function getIDFromConfigName($config_name, $config_prefix) {
    return substr($config_name, strlen($config_prefix . '.'));
  }

  /**
   * {@inheritdoc}
   */
  public function createFromStorageRecord(array $values) {
    return $this->getOriginalObject()->createFromStorageRecord($values);
  }

  /**
   * {@inheritdoc}
   */
  public function updateFromStorageRecord(ConfigEntityInterface $entity, array $values) {
    return $this->getOriginalObject()->updateFromStorageRecord($entity, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getAggregateQuery($conjunction = 'AND') {
    return $this->getOriginalObject()->getAggregateQuery($conjunction);
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrideFree($id) {
    return $this->getOriginalObject()->loadOverrideFree($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleOverrideFree(array $ids = NULL) {
    return $this->getOriginalObject()->loadMultipleOverrideFree($ids);
  }
}
