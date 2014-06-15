<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ViewsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Entity\BlockStorageDecorator;
use Drupal\webprofiler\Entity\EntityManagerWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about rendered views.
 */
class BlockDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /** @var $entityManager */
  private $entityManager;

  /**
   * @param EntityManagerWrapper $entityManager
   */
  public function __construct(EntityManagerWrapper $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $storages = $this->entityManager->getStorages();

    $this->data['blocks'] = array();
    if ($storages) {
      foreach ($storages as $storage) {
        /** @var BlockStorageDecorator $storage */
        foreach ($storage->getBlocks() as $block) {
          $this->data['blocks'][] = array(
            'id' => $block->id,
            'region' => $block->get('region'),
            'status' => $block->get('status'),
            'theme' => $block->get('theme'),
            'plugin' => $block->get('plugin'),
            'settings' => $block->get('settings'),
          );
        }
      }
    }
  }

  /**
   * @return array
   */
  public function getBlocks() {
    return $this->data['blocks'];
  }

  /**
   * @return int
   */
  public function getBlocksCount() {
    return count($this->data['blocks']);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'block';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Block');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total blocks: @count', array('@count' => $this->getBlocksCount()));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    if ($this->getBlocksCount()) {

      /** @var EntityManager $entity_manager */
      $entity_manager = \Drupal::service('entity.manager');
      $storage = $entity_manager->getStorage('block');

      $rows = array();
      foreach ($this->getBlocks() as $block) {
        $row = array();

        /** @var Block $entity */
        $entity = $storage->load($block['id']);

        $operations = array();
        if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
          $operations['edit'] = array(
              'title' => $this->t('Edit'),
              'weight' => 10,
            ) + $entity->urlInfo('edit-form')->toArray();
        }

        $row[] = $entity->id();
        $row[] = $block['settings']['label'];
        $row[] = $block['settings']['provider'];
        $row[] = ($block['region'] == -1) ? $this->t('No region') : $block['region'];
        $row[] = $block['theme'];
        $row[] = ($block['status']) ? $this->t('Enabled') : $this->t('Disabled');
        $row[] = $block['plugin'];
        $row[] = array(
          'data' => array(
            '#type' => 'operations',
            '#links' => $operations,
          ),
        );

        $rows[] = $row;
      }

      $header = array(
        $this->t('Id'),
        $this->t('Label'),
        $this->t('Provider'),
        $this->t('Region'),
        array(
          'data' => $this->t('Theme'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Status'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Plugin'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        $this->t('Operations'),
      );

      $build['title'] = array(
        array(
          '#markup' => '<h3>' . $this->t('Rendered blocks') . '</h3>',
        ),
      );

      $build['table'] = array(
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $header,
      );
    }


    return $build;
  }
}
