<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\FrontendDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about frontend performance.
 */
class FrontendDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {

  }

  /**
   * @param $data
   */
  public function setData($data) {
    $this->data['performance'] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'frontend';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Frontend');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    var_dump($this->data['performance']);

    return $build;
  }
}
