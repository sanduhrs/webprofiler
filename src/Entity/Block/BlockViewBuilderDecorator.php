<?php

namespace Drupal\webprofiler\Entity\Block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Class BlockViewBuilderDecorator
 */
class BlockViewBuilderDecorator extends BlockDecorator implements EntityViewBuilderInterface {

  /**
   * @param EntityViewBuilderInterface $controller
   */
  public function __construct(EntityViewBuilderInterface $controller) {
    parent::__construct($controller);

    $this->blocks = array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode, $langcode = NULL) {
    $this->getOriginalObject()->buildComponents($build, $entities, $displays, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $this->blocks[] = $entity;

    return $this->getOriginalObject()->view($entity, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    $this->blocks = array_merge($this->blocks, $entities);

    return $this->getOriginalObject()->viewMultiple($entities, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    $this->getOriginalObject()->resetCache($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function viewField(FieldItemListInterface $items, $display_options = array()) {
    return $this->getOriginalObject()->viewField($items, $display_options);
  }

  /**
   * {@inheritdoc}
   */
  public function viewFieldItem(FieldItemInterface $item, $display_options = array()) {
    return $this->getOriginalObject()->viewFieldItem($item, $display_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getOriginalObject()->getCacheTag();
  }

}
